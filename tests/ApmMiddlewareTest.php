<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Http\Request;
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
        $context = new ApmContext(new AlwaysOn());
        $middleware = new ApmMiddleware($context);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getContent')->andReturn('ok');
        $request->shouldReceive('method')->andReturn('post');
        $request->shouldReceive('fullUrl')->andReturn('http://localhost/path');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('status')->andReturn('200');
        $request->shouldReceive('all')->andReturn(['data' => 'example']);

        $closure = function () use ($request){
            return $request;
        };

        // When
        $middleware->handle($request, $closure);

        // Then
        $this->assertDatabaseHas('apm_requests', [
            'method' => 'post'
        ]);
    }
}