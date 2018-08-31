<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\ApmTestCase;
use Vistik\Apm\Middleware\ApmMiddleware;
use Vistik\Apm\Models\Request as ApmRequest;
use Vistik\Apm\Request\ApmContext;
use Vistik\Apm\Sampling\Chance;
use Vistik\Apm\Sampling\On;

class ApmMiddlewareTest extends ApmTestCase
{

    use InteractsWithDatabase;

    /** @test */
    public function can_save_requests()
    {
        // Given
        $this->migrate();

        $userId = rand(1, 18);
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $context = new ApmContext(new Chance(100));
        $middleware = new ApmMiddleware($context);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getContent')->andReturn('ok');
        $request->shouldReceive('method')->andReturn('post');
        $request->shouldReceive('fullUrl')->andReturn('http://localhost/path');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('status')->andReturn('200');
        $requestBody = ['data' => 'example'];
        $request->shouldReceive('all')->andReturn($requestBody);
        $request->shouldReceive('header')->andReturn('hey', 'hey-hey');
        $request->headers = [
            'header1' => 'hey',
            'header2' => 'hey-hey'
        ];

        $closure = function () use ($request) {
            return $request;
        };

        // When
        $middleware->handle($request, $closure);

        // Then
        $this->assertDatabaseHas('apm_requests', [
            'uuid'          => $context->getId(),
            'user_id'       => $userId,
            'request_body'  => json_encode($requestBody),
            'response_body' => 'ok',
            'headers'       => json_encode($request->headers),
            'url'           => 'http://localhost/path',
            'method'        => 'post',
            'ip'            => '127.0.0.1',
            'status_code'   => 200,
            'requested_at'  => $context->getStartedAt(),
        ]);

        $count = ApmRequest::where('response_time_ms', '>', 10)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function can_save_queries()
    {
        // Given
        $this->migrate();

        $userId = rand(1, 18);
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $context = new ApmContext(new Chance(100));
        $context->addQuery('select * from users', 3.45, [], 'testing');
        $context->addQuery('select * from users where id = ?', 1.23, [4], 'testing');

        $middleware = new ApmMiddleware($context);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getContent')->andReturn('ok');
        $request->shouldReceive('method')->andReturn('post');
        $request->shouldReceive('fullUrl')->andReturn('http://localhost/path');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('status')->andReturn('200');
        $requestBody = ['data' => 'example'];
        $request->shouldReceive('all')->andReturn($requestBody);
        $request->shouldReceive('header')->andReturn('hey', 'hey-hey');
        $request->headers = [
            'header1' => 'hey',
            'header2' => 'hey-hey'
        ];

        $closure = function () use ($request) {
            return $request;
        };

        // When
        $middleware->handle($request, $closure);

        // Then
        $this->assertDatabaseHas('apm_queries', [
            'request_id' => $context->getId(),
            'sql'        => 'select * from users',
            'time_ms'    => 3.45,
            'connection' => 'testing',
        ]);

        $this->assertDatabaseHas('apm_queries', [
            'request_id' => $context->getId(),
            'sql'        => 'select * from users where id = ?',
            'time_ms'    => 1.23,
            'connection' => 'testing',
        ]);

    }
}