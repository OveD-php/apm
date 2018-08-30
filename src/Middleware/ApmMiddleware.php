<?php

namespace Vistik\Apm\Middleware;

use Closure;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Vistik\Apm\Jobs\StoreQueries;
use Vistik\Apm\Jobs\StoreRequestData;
use Vistik\Apm\Request\RequestContext;
use Vistik\Apm\Request\RequestResponseData;

class ApmMiddleware
{

    use DispatchesJobs;
    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var integer
     */
    private $sampling;

    /**
     * ApmMiddleware constructor.
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
        $this->sampling = config('apm.samling', 100);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $requestId = $this->requestContext->getId();
        $userId = Auth::user() ? Auth::user()->id : 'n/a';

        $monolog = Log::getMonolog();
        $monolog->pushProcessor(function ($record) use ($userId, $requestId) {
            $record['extra']['user'] = $userId;
            $record['extra']['request_id'] = $requestId;

            return $record;
        });

        $response = $next($request);
        
        if (!$this->shouldSample()){
            return $response;
        }

        $responseContent = $response->getContent();

        $headers = collect($request->headers)
            ->keys()
            ->flip()
            ->map(function ($i, $header) use ($request) {
                return $request->header($header);
            })
            ->all();

        $requestResponseData = new RequestResponseData(
            $this->requestContext->getId(),
            Auth::id(),
            json_encode($request->all()), // This should not be all should be getContent()
            $request->method(),
            $request->fullUrl(),
            $request->ip(),
            $response->status(),
            $responseContent,
            $this->getResponseTimeInMs(),
            $headers,
            $this->requestContext->getStartedAt()
        );


        try {
            $this->dispatch(new StoreRequestData($requestResponseData));
        } catch (Exception $e) {
            // An exception in logging shouldn't terminate
            // the session and cause a 500 response!
            // Move on -->
            Log::error($e);
        }

        try {
            $this->dispatch(new StoreQueries($this->requestContext));
        } catch (Exception $e) {
            // An exception in logging shouldn't terminate
            // the session and cause a 500 response!
            // Move on -->
            Log::error($e);
        }

        return $response;
    }

    /**
     * @return int
     */
    protected function getResponseTimeInMs(): int
    {
        // Not defined when running tests
        if (defined('LARAVEL_START')) {
            return (int)((microtime(true) - LARAVEL_START) * 1000);
        }

        return 0.0;
    }

    protected function shouldSample(): bool
    {
        $random = mt_rand(0, 100);
        if ($random <= $this->sampling){
            return true;
        }

        return false;
    }
}
