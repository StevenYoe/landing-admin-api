<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

// User model extends Laravel's Authenticatable for authentication and API token support.
// This model is used by Sanctum for API authentication and user management.
class User extends Authenticatable
{
    use HasApiTokens;
    
    // The attributes that are mass assignable
    protected $fillable = [
        'id',      // User ID
        'name',    // User name
        'email',   // User email address
    ];
    
    // No need to specify a database connection, as this is only used for Sanctum authentication
    // But the model is still required to resolve 'Class not found' errors in relationships
}