<?php


namespace OveD\Apm\Filters;

use Illuminate\Http\Request;

interface FilterInterface
{
    public function shouldReject(Request $request): bool;
}
