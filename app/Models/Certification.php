<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Certification model represents the 'pazar_certifications' table in the 'pazar' database connection.
// It stores information about certifications, including multilingual labels, titles, descriptions, and an image.
class Certification extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_certifications';
    // The primary key for the table
    protected $primaryKey = 'c_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'c_label_id',        // Label in Indonesian
        'c_label_en',        // Label in English
        'c_title_id',        // Title in Indonesian
        'c_title_en',        // Title in English
        'c_description_id',  // Description in Indonesian
        'c_description_en',  // Description in English
        'c_image'            // Image path or filename
    ];
}