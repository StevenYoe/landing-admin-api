<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Footer model represents the 'pazar_footer' table in the 'pazar' database connection.
// It stores information about footer elements, including multilingual labels, descriptions, icons, and links.
class Footer extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_footer';
    // The primary key for the table
    protected $primaryKey = 'f_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'f_type',            // Type/category of the footer item
        'f_label_id',        // Label in Indonesian
        'f_label_en',        // Label in English
        'f_description_id',  // Description in Indonesian
        'f_description_en',  // Description in English
        'f_icon',            // Icon class or path
        'f_link'             // URL or link for the footer item
    ];
}