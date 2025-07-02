<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    
    protected $fillable = [
        'id',
        'name',
        'email',
    ];
    
    // Tidak perlu database connection, karena ini hanya model untuk digunakan oleh Sanctum
    // Tapi tetap diperlukan untuk menyelesaikan error Class not found
}