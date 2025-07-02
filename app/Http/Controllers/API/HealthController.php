<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Controller for checking the health status of the API and its services
class HealthController extends Controller
{
    /**
     * Check API health status, including database connectivity and app version.
     *
     * @return \Illuminate\Http\Response
     */
    public function check()
    {
        $dbStatus = 'OK';
        
        try {
            // Check database connection by attempting to get a PDO instance
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // If database connection fails, set status to error with message
            $dbStatus = 'ERROR: ' . $e->getMessage();
        }

        // Return a JSON response with health status, timestamp, service statuses, and app version
        return response()->json([
            'status' => 'UP',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbStatus,
                'app' => 'OK'
            ],
            'version' => config('app.version', '1.0.0')
        ]);
    }
}