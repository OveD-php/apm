<?php

namespace Vistik\Apm\Sampling;

interface SamplerInterface
{
    public function shouldSample(): bool;
}