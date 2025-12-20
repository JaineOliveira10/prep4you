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
        // Se for uma requisição AJAX, API ou shipments update-status, garante que sempre retorna JSON
        if ($request->expectsJson() || $request->is('shipments/*/update-status') || $this->isApiRequest($request)) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Se for uma requisição AJAX ou para API, garante o header JSON
        if ($request->expectsJson() || $request->is('shipments/*/update-status') || $this->isApiRequest($request)) {
            $response->header('Content-Type', 'application/json; charset=UTF-8');
        }

        return $response;
    }

    /**
     * Verifica se é uma requisição API
     */
    private function isApiRequest(Request $request)
    {
        return $request->header('X-Requested-With') === 'XMLHttpRequest' ||
               $request->header('Accept') === 'application/json' ||
               $request->is('api/*');
    }
}
