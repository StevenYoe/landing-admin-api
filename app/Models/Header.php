<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Header model represents the 'pazar_headers' table in the 'pazar' database connection.
// It stores information about page headers, including multilingual titles, descriptions, page name, and an image.
class Header extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_headers';
    // The primary key for the table
    protected $primaryKey = 'h_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'h_title_id',        // Title in Indonesian
        'h_title_en',        // Title in English
        'h_page_name',       // Name of the page this header belongs to
        'h_description_id',  // Description in Indonesian
        'h_description_en',  // Description in English
        'h_image'            // Image path or filename
    ];
}