<?php

namespace Vistik\Apm\Http\Middleware;

use Apm\Jobs\StoreQueries;
use Apm\Jobs\StoreRequestData;
use Apm\Request\RequestContext;
use Apm\Request\RequestResponseData;
use Carbon\Carbon;
use Closure;
use DateTimeZone;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApmMiddleware
{

    use DispatchesJobs;
    /**
     * @var RequestContext
     */
    private $requestContext;

    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
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
        $userId = $request->getUser() !== null ? $request->getUser() : 'n/a';

        $monolog = Log::getMonolog();
        $monolog->pushProcessor(function ($record) use ($userId, $requestId) {
            $record['extra']['user'] = $userId;
            $record['extra']['request_id'] = $requestId;
            return $record;
        });

        $response = $next($request);

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
        if (defined('LARAVEL_START')){
            return (int)((microtime(true) - LARAVEL_START) * 1000);
        }

        return 0.0;
    }
}