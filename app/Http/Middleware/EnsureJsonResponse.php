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
        if (!$request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Accept header must be application/json',
            ], 406); // HTTP 406 Not Acceptable
        }

        $response = $next($request);

        // Jika respons bukan JSON, tambahkan Content-Type header
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $contentType = $response->headers->get('Content-Type');
            if (strpos($contentType, 'application/json') === false) {
                $response->headers->set('Content-Type', 'application/json');
            }
        }

        return $response;
    }
}
