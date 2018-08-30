<?php

namespace Vistik\Apm\Sampling;

class AlwaysOff implements SamplerInterface
{
    public function shouldSample(): bool
    {
        return false;
    }
}