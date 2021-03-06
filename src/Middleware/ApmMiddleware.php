<?php

namespace OveD\Apm\Middleware;

use Closure;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OveD\Apm\Filters\FilterInterface;
use OveD\Apm\Jobs\StoreQueries;
use OveD\Apm\Jobs\StoreRequestData;
use OveD\Apm\Request\ApmContext;
use OveD\Apm\Request\RequestResponseData;

class ApmMiddleware
{

    use DispatchesJobs;
    /**
     * @var ApmContext
     */
    private $apmContext;

    /**
     * ApmMiddleware constructor.
     * @param ApmContext $apmContext
     */
    public function __construct(ApmContext $apmContext)
    {
        $this->apmContext = $apmContext;
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
        $filters = $this->apmContext->getFilters();

        $requestId = $this->apmContext->getId();

        $monolog = Log::getMonolog();
        $monolog->pushProcessor(function ($record) use ($requestId) {
            $record['extra']['request_id'] = $requestId;

            return $record;
        });

        $response = $next($request);

        if ($this->shouldBeRejected($request, $filters)) {
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
            $this->apmContext->getId(),
            Auth::id(),
            json_encode($request->all()), // This should not be all should be getContent()
            $request->method(),
            $request->fullUrl(),
            $request->ip(),
            $response->status(),
            $responseContent,
            $this->getResponseTimeInMs(),
            $headers,
            $this->apmContext->getStartedAt()
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
            $this->dispatch(new StoreQueries($this->apmContext));
        } catch (Exception $e) {
            // An exception in logging shouldn't terminate
            // the session and cause a 500 response!
            // Move on -->
            Log::error($e);
        }

        return $response;
    }

    protected function getResponseTimeInMs(): float
    {
        // Not defined when running tests
        if (defined('LARAVEL_START')) {
            return (int)((microtime(true) - LARAVEL_START) * 1000);
        }

        return 0.0;
    }

    /**
     * @param $request
     * @param $filters
     * @return bool
     */
    protected function shouldBeRejected($request, $filters): bool
    {
        return $filters->reduce(function (bool $carry, FilterInterface $filter) use ($request) {
            return $carry || $filter->shouldReject($request);
        }, false);
    }
}
