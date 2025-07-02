<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// CompanyProfile model represents the 'pazar_company_profile' table in the 'pazar' database connection.
// It stores information about the company's profile, including multilingual descriptions and a type field.
class CompanyProfile extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_company_profile';
    // The primary key for the table
    protected $primaryKey = 'cp_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'cp_description_id', // Description in Indonesian
        'cp_description_en', // Description in English
        'cp_type'           // Type/category of the company profile
    ];
}