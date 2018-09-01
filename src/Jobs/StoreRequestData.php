<?php

namespace Vistik\Apm\Jobs;

use Illuminate\Support\Facades\Log;
use Vistik\Apm\Models\Request;
use Vistik\Apm\Request\RequestResponseData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreRequestData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var RequestResponseData
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RequestResponseData $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('apm.saveRequestsToLog', false)){
            Log::debug(sprintf("Request (%sms): %s [%s]: %s", $this->data->getResponseTimeMilliseconds(), $this->data->getStatusCode(), $this->data->getMethod(), $this->data->getUrl()));
        }
        Request::create([
            'uuid'             => $this->data->getUuid(),
            'user_id'          => $this->data->getUserId(),
            'request_body'     => $this->data->getRequestBody(),
            'method'           => $this->data->getMethod(),
            'url'              => $this->data->getUrl(),
            'ip'               => $this->data->getIp(),
            'status_code'      => $this->data->getStatusCode(),
            'response_body'    => $this->data->getResponseBody(),
            'response_time_ms' => $this->data->getResponseTimeMilliseconds(),
            'headers'          => $this->data->getHeaders(),
            'requested_at'     => $this->data->getRequestedAt(),
        ]);
    }
}
