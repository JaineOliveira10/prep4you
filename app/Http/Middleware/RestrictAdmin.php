<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (strtolower(auth()->user()->type) === 'admin') {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}