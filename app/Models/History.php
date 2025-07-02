<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// History model represents the 'pazar_history' table in the 'pazar' database connection.
// It stores information about the company's history, including year, multilingual descriptions, and an image.
class History extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_history';
    // The primary key for the table
    protected $primaryKey = 'hs_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'hs_year',            // Year of the historical event
        'hs_description_id',  // Description in Indonesian
        'hs_description_en',  // Description in English
        'hs_image'            // Image path or filename
    ];
}