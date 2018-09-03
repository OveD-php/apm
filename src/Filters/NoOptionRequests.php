<?php

namespace OveD\Apm\Filters;

use Illuminate\Http\Request;

class NoOptionRequests implements FilterInterface
{

    public function shouldReject(Request $request): bool
    {
        return $request->method() == 'OPTIONS';
    }
}