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
        // Cek jika header Accept tidak berisi application/json
        // Paksa penggunaan Accept: application/json
        // if (!$request->hasHeader('Accept') || $request->header('Accept') !== 'application/json') {
        //     $request->headers->set('Accept', 'application/json');
        // }

        $response = $next($request);

        // Jika respons bukan JSON, tambahkan Content-Type header
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $contentType = $response->headers->get('Content-Type');
            $accept = $response->headers->get('Accept');
            if (strpos($accept, 'application/json') === false) {
                $response->headers->set('Accept', 'application/json');
            }
            if (strpos($contentType, 'application/json') === false) {
                $response->headers->set('Content-Type', 'application/json');
            }

            
            
        }

        return $response;
    }
}
