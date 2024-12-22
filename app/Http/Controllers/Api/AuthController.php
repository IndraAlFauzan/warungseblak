<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{


    public function register(Request $request)
    {
        // Validasi request termasuk validasi role
        $validatedData = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,kasir',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validatedData->errors()->first(),
            ], 400);
        }

        try {
            // Buat user baru
            $user = new User;
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->role     = $request->role;
            $user->save();

            $token = Auth::login($user);

            // Return response sukses
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data'    => $user,
                'token'   => $token,
            ], 201);
        } catch (Exception $e) {
            // Return response gagal
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Attempt login dengan email dan password
        if (!$token = Auth::guard('api')->attempt($validatedData)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Return token jika sukses
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => Auth::guard('api')->user(),
            ],
        ], 200);
    }

    public function me()
    {
        // try {
            $user = Auth::guard('api')->user();

        //     if (!$user) {
        //         throw new \Exception('No authenticated user found');
        //     }

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => $user,
            ], 200);
        // } catch (\Exception $e) {
        //     Log::error('Error in me() function:', ['error' => $e->getMessage()]);
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to retrieve user profile',
        //         'error'   => $e->getMessage(),
        //     ], 500);
        // }
    }


    public function logout()
    {
        try {
            Auth::guard('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = Auth::guard('api')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function updateProfile(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        try {

            // Ambil user yang sedang login
            $user = Auth::guard('api')->user();

            // Update data user
            $user->name  = $validatedData['name'];
            $user->email = $validatedData['email'];

            // Update password jika dimasukkan
            if (isset($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User profile updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user profile',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
