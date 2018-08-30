<?php

namespace Vistik\Apm\Sampling;

class AlwaysOn implements SamplerInterface
{

    public function shouldSample(): bool
    {
        return true;
    }
}