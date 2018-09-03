<?php

namespace OveD\Apm\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OveD\Apm\Models\Query;
use OveD\Apm\Request\ApmContext;

class StoreQueries implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var ApmContext
     */
    private $apmContext;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ApmContext $apmContext)
    {
        $this->apmContext = $apmContext;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $queries = $this->apmContext->getQueries();

        foreach ($queries as $query) {
            if (config('apm.saveQueriesToLog', false)) {
                Log::debug(sprintf(
                    "Query(%s, time: %sms): %s",
                    $query['connection'],
                    $query['time_ms'],
                    $query['query']
                ));
            }
            Query::create([
                'sql'        => $query['query'],
                'time_ms'    => $query['time_ms'],
                'connection' => $query['connection'],
                'request_id' => $this->apmContext->getId(),
            ]);
        }
    }
}
