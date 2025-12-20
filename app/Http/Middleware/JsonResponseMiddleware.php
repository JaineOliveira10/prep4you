<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Se for uma requisição AJAX ou API, garante que sempre retorna JSON
        if ($request->expectsJson() || $request->is('shipments/*/update-status')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Se for uma requisição AJAX ou para API, garante o header JSON
        if ($request->expectsJson() || $request->is('shipments/*/update-status')) {
            if (!$response->headers->has('Content-Type')) {
                $response->header('Content-Type', 'application/json');
            }
        }

        return $response;
    }
}
