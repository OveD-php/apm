<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use OveD\Apm\Jobs\StoreQueries;
use OveD\Apm\Jobs\StoreRequestData;
use OveD\Apm\Request\ApmContext;
use OveD\Apm\Request\RequestResponseData;
use OveD\Apm\Sampling\Chance;

class JobsTest extends ApmTestCase
{

    /** @test */
    public function can_write_queries_to_log()
    {
        // Given
        $this->migrate();
        Log::shouldReceive('debug')->twice();

        $this->app['config']->set('apm.saveQueriesToLog', true);
        $context = new ApmContext(new Chance(100));
        $context->addQuery('select * from users', 3.45, [], 'testing');
        $context->addQuery('select * from users where id = ?', 1.23, [4], 'testing');

        $job = new StoreQueries($context);

        // When
        $job->handle();

        // Then
        // Assertion is Log::shouldReceive('debug')->twice();
    }

    /** @test */
    public function can_write_requests_to_log()
    {
        // Given
        $this->migrate();
        Log::shouldReceive('debug')->once();

        $this->app['config']->set('apm.saveRequestsToLog', true);

        $data = new RequestResponseData(
            Uuid::uuid4()->toString(),
            1,
            'hello!',
            'GET',
            'http://localhost/path',
            '127.0.0.1',
            200,
            'world?',
            6.66,
            ['random' => 'header'],
            Carbon::now()
        );

        $job = new StoreRequestData($data);

        // When
        $job->handle();

        // Then
        // Assertion is Log::shouldReceive('debug')->once();
    }
}