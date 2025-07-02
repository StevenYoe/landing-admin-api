<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Controller for handling authentication-related API endpoints
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
            // Retrieve the Bearer token from the request header
            $token = $request->bearerToken();
            
            // If no token is provided, return an unauthorized response
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No token provided'
                ], 401);
            }
            
            // Get the admin API URL from config or environment
            $adminApiUrl = config('api.auth_api_url', env('AUTH_API_BASE_URL'));
            
            // Ensure the authentication service URL is configured
            if (!$adminApiUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication service not configured properly'
                ], 500);
            }
            
            // Call the admin API to validate the token and retrieve user data
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10) // Set a timeout for the request
                ->get($adminApiUrl . '/me');
            
            // If the response status is not 200, the token is invalid or expired
            if ($response->status() !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }
            
            // Parse the user data from the response
            $userData = $response->json();
            
            // Return user data including roles for permission checking
            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'data' => $userData['data'] ?? $userData ?? null
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Error validating token: ' . $e->getMessage()
            ], 500);
        }
    }
}