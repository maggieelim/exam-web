<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureContext
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('context')) {
            session(['context' => 'pssk']);
        }

        return $next($request);
    }
}
