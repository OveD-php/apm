<?php

namespace Tests;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use OveD\Apm\Models\Query;
use OveD\Apm\Models\Request;

class ModelTest extends ApmTestCase
{
    /** @test */
    public function requests_have_queries()
    {
        // Given
        $this->migrate();

        $requestId = Uuid::uuid4()->toString();
        $request = Request::create([
            'uuid'             => $requestId,
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

        Query::create([
            'request_id' => $requestId,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 5',
            'time_ms' => 1.38,
            'connection' => 'testing',
        ]);

        Query::create([
            'request_id' => Uuid::uuid4()->toString(),
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 15',
            'time_ms' => 2.66,
            'connection' => 'testing'
        ]);

        // When
        $related = $request->queries;

        // Then
        $this->assertCount(1, $related);
        $this->assertEquals('select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 5', $related[0]->sql);
        $this->assertEquals(1.38, $related[0]->time_ms);
        $this->assertEquals('testing', $related[0]->connection);
    }

    /** @test */
    public function queries_has_a_request()
    {
        // Given
        $this->migrate();

        $requestId = Uuid::uuid4()->toString();
        Request::create([
            'uuid'             => $requestId,
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

        $query = Query::create([
            'request_id' => $requestId,
            'sql' => 'select * from "apm_requests" group by "url", "method" order by "response_time_ms" desc limit 5',
            'time_ms' => 1.38,
            'connection' => 'testing',
        ]);

        // When
        $related = $query->request;

        // Then
        $this->assertTrue($related instanceof Request);
        $this->assertEquals($requestId, $related->uuid);
    }
}