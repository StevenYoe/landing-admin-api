<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Verify if token is valid by calling the admin-api
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateToken(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No token provided'
                ], 401);
            }
            
            $adminApiUrl = config('api.auth_api_url', env('AUTH_API_BASE_URL'));
            
            // Pastikan URL sudah benar dan lengkap
            if (!$adminApiUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication service not configured properly'
                ], 500);
            }
            
            // Call the admin-api to validate the token and get user data
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10) // Add timeout
                ->get($adminApiUrl . '/me');
            
            if ($response->status() !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }
            
            $userData = $response->json();
            
            // Return user data including roles for permission checking
            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'data' => $userData['data'] ?? $userData ?? null
            ]);
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Error validating token: ' . $e->getMessage()
            ], 500);
        }
    }
}