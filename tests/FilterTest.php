<?php

namespace Tests;

use Illuminate\Http\Request;
use Mockery;
use OveD\Apm\Filters\NoOptionRequests;
use OveD\Apm\Filters\Sampling;

class FilterTest extends ApmTestCase
{

    /** @test */
    public function can_filter_options_requests()
    {
        // Given
        $filter = new NoOptionRequests();

        // When
        $mock = \Mockery::mock(Request::class);
        $mock->shouldReceive('method')->andReturn('OPTIONS');
        $reject = $filter->shouldReject($mock);

        // Then
        $this->assertEquals(true, $reject);
    }

    /** @test */
    public function can_turn_off_apm()
    {
        // Given
        $sampling = new Sampling(0);

        // When
        $mock = Mockery::mock(Request::class);
        for ($i =0; $i < 10000; $i++){
            $this->assertTrue($sampling->shouldReject($mock));
        }
        // Then
    }

    /** @test */
    public function can_turn_on_apm()
    {
        // Given
        $sampling = new Sampling(100);

        // When
        $mock = Mockery::mock(Request::class);
        for ($i =0; $i < 10000; $i++){
            $this->assertFalse($sampling->shouldReject($mock));
        }
        // Then
    }
}