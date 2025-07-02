<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

// AuthServiceProvider is responsible for registering authentication and authorization services.
// This includes custom guards, policies, and any logic related to user authentication.
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * Maps models to their corresponding policy classes for authorization.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ...existing code...
    ];

    /**
     * Register any authentication / authorization services.
     * This method is called during application bootstrapping.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Register a custom token guard for our API
        // This guard authenticates users by validating an external token via an internal API call
        Auth::viaRequest('external-token', function ($request) {
            $token = $request->bearerToken();
            
            if (!$token) {
                return null;
            }
            
            try {
                // Call validateToken API internally to check if the token is valid
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->get(config('app.url') . '/api/validate-token');
                
                if ($response->successful() && $response->json('success') === true) {
                    $userData = $response->json('data');
                    
                    if (!$userData) {
                        return null;
                    }
                    
                    // Create a user instance that Laravel can use for authorization
                    // No need to save this in the database
                    return new User([
                        'id' => $userData['id'] ?? 1,  // Use ID from API or default
                        'name' => $userData['name'] ?? 'API User',
                        'email' => $userData['email'] ?? 'api@example.com'
                    ]);
                }
                
                return null;
            } catch (\Exception $e) {
                // Log any authentication errors for debugging
                \Log::error('Auth error: ' . $e->getMessage());
                return null;
            }
        });
    }
}