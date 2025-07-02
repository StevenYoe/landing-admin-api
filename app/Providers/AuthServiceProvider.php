<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Register a custom token guard for our API
        Auth::viaRequest('external-token', function ($request) {
            $token = $request->bearerToken();
            
            if (!$token) {
                return null;
            }
            
            try {
                // Call validateToken API internally
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
                \Log::error('Auth error: ' . $e->getMessage());
                return null;
            }
        });
    }
}