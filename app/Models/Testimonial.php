<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Testimonial model represents the 'pazar_testimonial' table in the 'pazar' database connection.
// It stores information about testimonials, including name, multilingual descriptions, type, gender, and image.
class Testimonial extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_testimonial';
    // The primary key for the table
    protected $primaryKey = 't_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        't_name',             // Name of the person giving the testimonial
        't_description_id',   // Testimonial description in Indonesian
        't_description_en',   // Testimonial description in English
        't_type',             // Type/category of testimonial
        't_gender',           // Gender of the person
        't_image'             // Image path or filename
    ];
}