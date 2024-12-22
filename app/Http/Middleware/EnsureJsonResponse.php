<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Pastikan respons selalu JSON
        if ($response instanceof \Symfony\Component\HttpFoundation\Response && !$response->headers->contains('Content-Type', 'application/json')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
