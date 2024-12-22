<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::guard('api')->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have access to this resource',
            ], 403);
        }

        return $next($request);
    }
}
