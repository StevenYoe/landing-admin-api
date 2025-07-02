<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// CareerInfo model represents the 'career_careerinfo' table in the 'pazar' database connection.
// It stores information about career highlights, including multilingual titles, descriptions, and an image.
class CareerInfo extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_careerinfo';
    // The primary key for the table
    protected $primaryKey = 'ci_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'ci_title_id',      // Title in Indonesian
        'ci_title_en',      // Title in English
        'ci_description_id',// Description in Indonesian
        'ci_description_en',// Description in English
        'ci_image'          // Image path or filename
    ];
}