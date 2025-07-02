<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Popup model represents the 'pazar_popup' table in the 'pazar' database connection.
// It stores information about popup banners, including image, link, and active status.
class Popup extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_popup';
    // The primary key for the table
    protected $primaryKey = 'pu_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'pu_image',      // Image path or filename for the popup
        'pu_link',       // URL or link for the popup
        'pu_is_active'   // Boolean indicating if the popup is active
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'pu_is_active' => 'boolean' // Ensure 'pu_is_active' is always treated as a boolean
    ];
}