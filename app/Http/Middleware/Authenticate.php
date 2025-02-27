<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
    
        if (empty($guards)) {
            $guards = [null];
        }

        // Check if the user is authenticated
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // For API requests, return a 401 Unauthorized response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // For web requests, redirect to the login route (if needed)
        return redirect()->route('login');
    }
}
