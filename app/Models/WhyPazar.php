<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// WhyPazar model represents the 'pazar_why_pazar' table in the 'pazar' database connection.
// It stores information about reasons to choose Pazar, including multilingual titles, descriptions, and an image.
class WhyPazar extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_why_pazar';
    // The primary key for the table
    protected $primaryKey = 'w_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'w_title_id',        // Title in Indonesian
        'w_title_en',        // Title in English
        'w_description_id',  // Description in Indonesian
        'w_description_en',  // Description in English
        'w_image'            // Image path or filename
    ];
}