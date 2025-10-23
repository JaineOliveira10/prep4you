<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictClient
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->type === 'Client') {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}