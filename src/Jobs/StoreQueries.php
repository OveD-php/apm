<?php

namespace Vistik\Apm\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Vistik\Apm\Models\Query;
use Vistik\Apm\Request\ApmContext;

class StoreQueries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var ApmContext
     */
    private $requestContext;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ApmContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $queries = $this->requestContext->getQueries();

        foreach ($queries as $query) {
            if (config('apm.saveQueriesToLog', false)){
                Log::debug(sprintf("Query(%s, time: %sms): %s", $query['connection'], $query['time_ms'], $query['query']));
            }
            Query::create([
                'sql'        => $query['query'],
                'time_ms'    => $query['time_ms'],
                'connection' => $query['connection'],
                'request_id' => $this->requestContext->getId(),
            ]);
        }
    }
}
