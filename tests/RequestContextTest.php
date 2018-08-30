<?php

namespace Tests\Unit;

use Apm\Request\RequestContext;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestContextTest extends TestCase
{

    /** @test */
    public function request_context_has_an_uuid()
    {
        // Given
        $context = new RequestContext();

        // When
        $uuid = $context->getId();

        // Then
        $this->assertRegExp('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', $uuid);
    }

    /** @test */
    public function request_context_has_started_at()
    {
        // Given
        $context = new RequestContext();

        // When
        $startedAt = $context->getStartedAt();

        // Then
        $this->assertEquals(Carbon::now()->timestamp, $startedAt->timestamp, 'Must be within 1 sec', 1.0);
    }

    /** @test */
    public function can_add_database_query()
    {
        // Given
        config(['apm.showBindings' => false]);
        $context = new RequestContext();

        // When
        $time = rand(1, 666);
        $context->addQuery("select * from \"users\" where \"id\" = ? limit 1", $time, ['1'], 'testing');

        // Then
        $this->assertEquals([
            [
                'query'      => "select * from \"users\" where \"id\" = ? limit 1",
                'time_ms'    => $time,
                'bindings'   => ['1'],
                'connection' => 'testing'
            ]
        ], $context->getQueries());
    }

    /** @test */
    public function can_add_database_query_and_add_bindings()
    {
        // Given
        config(['apm.showBindings' => true]);
        $context = new RequestContext();

        // When
        $id = rand(188, 881);
        $context->addQuery("select * from \"users\" where \"id\" = ? limit 1", 6.66, [$id], 'testing');

        // Then
        $this->assertEquals([
            [
                'query'      => "select * from \"users\" where \"id\" = '$id' limit 1",
                'time_ms'    => 6.66,
                'bindings'   => [$id],
                'connection' => 'testing'
            ]
        ], $context->getQueries());
    }

    /** @test */
    public function can_bind_multiple()
    {
        // Given
        config(['apm.showBindings' => true]);
        $context = new RequestContext();

        // When
        $id = rand(188, 881);
        $something = Str::random();

        $context->addQuery("select * from \"users\" where \"id\" = ? and \"something\" != ? limit 1", 6.66, [$id, $something], 'testing');

        // Then
        $this->assertEquals([
            [
                'query'      => "select * from \"users\" where \"id\" = '$id' and \"something\" != '$something' limit 1",
                'time_ms'    => 6.66,
                'bindings'   => [$id, $something],
                'connection' => 'testing'
            ]
        ], $context->getQueries());
    }

    /** @test */
    public function can_handle_zero_bindings()
    {
        // Given
        config(['apm.showBindings' => true]);
        $context = new RequestContext();

        // When
        $context->addQuery("select * from \"users\" limit 1", 6.66, [], 'testing');

        // Then
        $this->assertEquals([
            [
                'query'      => "select * from \"users\" limit 1",
                'time_ms'    => 6.66,
                'bindings'   => [],
                'connection' => 'testing'
            ]
        ], $context->getQueries());
    }
}
