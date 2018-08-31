<?php

namespace Tests;

use Vistik\Apm\Sampling\Chance;

class SamplingTest extends ApmTestCase
{

    /** @test */
    public function can_turn_off_apm()
    {
        // Given
        $sampling = new Chance(0);

        // When
        for ($i =0; $i < 10000; $i++){
            $this->assertFalse($sampling->shouldSample());
        }
        // Then
    }

    /** @test */
    public function can_turn_on_apm()
    {
        // Given
        $sampling = new Chance(100);

        // When
        for ($i =0; $i < 10000; $i++){
            $this->assertTrue($sampling->shouldSample());
        }
        // Then
    }
}