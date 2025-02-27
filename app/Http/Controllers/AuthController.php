<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'is_admin' => false,

        ]);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // User Login
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $user = Auth::user();
        Log::info('User logged in:', [
            'user_id' => $user->id,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ]);
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }


    //Logout user || admin
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();

            Log::info('Token: ' . $token);

            if (!$token) {
                Log::error('Token not provided');
                return response()->json(['message' => 'Token not provided'], 400);
            }
            JWTAuth::invalidate($token);
            Log::info('Logout successful');
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to logout', 'error' => $e->getMessage()], 500);
        }
    }
}
