<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\ApmTestCase;
use Vistik\Apm\Middleware\ApmMiddleware;
use Vistik\Apm\Request\ApmContext;
use Vistik\Apm\Sampling\AlwaysOn;

class ApmMiddlewareTest extends ApmTestCase
{

    use InteractsWithDatabase;

    /** @test */
    public function can_sample()
    {
        // Given
        $this->migrate();

        $userId = rand(1, 18);
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);

        $context = new ApmContext(new AlwaysOn());
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
    }
}