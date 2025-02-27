<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Log the user and token details
            Log::info('Authenticated user:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ]);

            if ($user->is_admin) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You do not have admin access.'], 403);
    }
}
