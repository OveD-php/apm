<?php

namespace OveD\Apm\Filters;

use Illuminate\Http\Request;

class Sampling implements FilterInterface
{
    /**
     * @var int
     */
    private $chance;

    public function __construct(int $chance)
    {
        $this->chance = $chance;
    }

    public function shouldReject(Request $request): bool
    {
        $random = mt_rand(1, 100);
        if ($random <= $this->chance) {
            return false;
        }

        return true;
    }
}