<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully. Please log in.',
                'user' => $user->only(['id', 'username', 'email']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User registration failed.',
                'details' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Authenticate a user and return a JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Could not create token'], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => auth()->user()->only(['id', 'username', 'email']),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the authenticated User's data.
     * This route will be protected by 'jwt.auth' middleware.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()->only(['id', 'username', 'email'])
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     * This route will be protected by 'jwt.auth' middleware.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to logout'], 500);
        }
    }

    /**
     * Refresh a token.
     * This route will be protected by 'jwt.refresh' middleware.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // Token baru sudah ditambahkan ke header oleh RefreshTokenMiddleware
        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully. Check Authorization header for new token.'
        ]);
    }
}