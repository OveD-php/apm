<?php

namespace OveD\Apm\Sampling;

interface SamplerInterface
{
    public function shouldSample(): bool;
}
