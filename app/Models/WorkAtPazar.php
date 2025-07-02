<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// WorkAtPazar model represents the 'career_work_at_pazar' table in the 'pazar' database connection.
// It stores information about working at Pazar, including multilingual titles, descriptions, and type.
class WorkAtPazar extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_work_at_pazar';
    // The primary key for the table
    protected $primaryKey = 'wap_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'wap_title_id',        // Title in Indonesian
        'wap_title_en',        // Title in English
        'wap_description_id',  // Description in Indonesian
        'wap_description_en',  // Description in English
        'wap_type'             // Type/category of the work at Pazar item
    ];
}