<?php

namespace Tests;

use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use OveD\Apm\Models\Query;
use OveD\Apm\Models\Request;
use OveD\Apm\Reports\QueryReports;
use OveD\Apm\Reports\RequestReports;

class QueryReportTest extends ApmTestCase
{

    /** @test */
    public function can_get_top_2_slowest_queries()
    {
        // Given
        $this->migrate();

        $requestId1 = Uuid::uuid4()->toString();
        Request::create([
            'uuid'             => $requestId1,
            'user_id'          => 1,
            'url'              => 'http://localhost/path',
            'response_body'    => 'hello!',
            'request_body'     => 'world?',
            'headers'          => json_encode(['random' => 'header']),
            'method'           => 'GET',
            'response_time_ms' => 6.66,
            'ip'               => '127.0.0.1',
            'status_code'      => 200,
            'requested_at'     => Carbon::now()
        ]);

        $requestId2 = Uuid::uuid4()->toString();
        Request::create([
            'uuid'             => $requestId2,
            'user_id'          => 1,
            'url'              => 'http://localhost/another',
            'response_body'    => 'hello!',
            'request_body'     => 'world?',
            'headers'          => json_encode(['random' => 'header']),
            'method'           => 'GET',
            'response_time_ms' => 7.77,
            'ip'               => '127.0.0.1',
            'status_code'      => 200,
            'requested_at'     => Carbon::now()
        ]);

        Query::create([
            'request_id' => $requestId1,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 5',
            'time_ms' => 1.38,
            'connection' => 'testing',
        ]);

        $q1 = Query::create([
            'request_id' => $requestId1,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 15',
            'time_ms' => 2.66,
            'connection' => 'testing'
        ]);

        $q2 = Query::create([
            'request_id' => $requestId2,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 52',
            'time_ms' => 7.38,
            'connection' => 'testing',
        ]);

        Query::create([
            'request_id' => $requestId2,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 152',
            'time_ms' => 0.66,
            'connection' => 'testing'
        ]);

        $report = new QueryReports();
        $queries = $report->getSlowest(2);

        $this->assertEquals([$q2->id, $q1->id], $queries->pluck('id')->toArray());
    }

    /** @test */
    public function can_get_top_2_slowest_endpoints()
    {
        // Given
        $this->artisan('migrate', ['--database' => 'testbench']);

        $requestId1 = Uuid::uuid4()->toString();
        $r1 = Request::create([
            'uuid'             => $requestId1,
            'user_id'          => 1,
            'url'              => 'http://localhost/path',
            'response_body'    => 'hello!',
            'request_body'     => 'world?',
            'headers'          => json_encode(['random' => 'header']),
            'method'           => 'GET',
            'response_time_ms' => 6.66,
            'ip'               => '127.0.0.1',
            'status_code'      => 200,
            'requested_at'     => Carbon::now()
        ]);

        $requestId2 = Uuid::uuid4()->toString();
        $r2 = Request::create([
            'uuid'             => $requestId2,
            'user_id'          => 1,
            'url'              => 'http://localhost/another',
            'response_body'    => 'hello!',
            'request_body'     => 'world?',
            'headers'          => json_encode(['random' => 'header']),
            'method'           => 'GET',
            'response_time_ms' => 7.77,
            'ip'               => '127.0.0.1',
            'status_code'      => 200,
            'requested_at'     => Carbon::now()
        ]);

        $q1 = Query::create([
            'request_id' => $requestId1,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 5',
            'time_ms' => 1.38,
            'connection' => 'testing',
        ]);

        $q2 = Query::create([
            'request_id' => $requestId1,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 15',
            'time_ms' => 2.66,
            'connection' => 'testing'
        ]);

        $q3 = Query::create([
            'request_id' => $requestId2,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 52',
            'time_ms' => 7.38,
            'connection' => 'testing',
        ]);

        $q4 = Query::create([
            'request_id' => $requestId2,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 152',
            'time_ms' => 0.66,
            'connection' => 'testing'
        ]);

        $report = new RequestReports();
        $requests = $report->getSlowest(2);

        $this->assertEquals([$r2->uuid, $r1->uuid], $requests->pluck('uuid')->toArray());
        $this->assertEquals([$q3->id, $q4->id], $requests[0]->queries->pluck('id')->toArray());
        $this->assertEquals([$q2->id, $q1->id], $requests[1]->queries->pluck('id')->toArray());
    }
}