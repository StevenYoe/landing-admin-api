<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Employment model represents the 'career_employments' table in the 'pazar' database connection.
// It stores information about employment types and their relationships to users and vacancies.
class Employment extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_employments';
    // The primary key for the table
    protected $primaryKey = 'e_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'e_created_at';
    const UPDATED_AT = 'e_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'e_title_id',      // Employment type title in Indonesian
        'e_title_en',      // Employment type title in English
        'e_created_by',    // User ID who created the employment type
        'e_updated_by'     // User ID who last updated the employment type
    ];

    /**
     * Get the vacancies for the employment type.
     * Defines a one-to-many relationship with the Vacancy model.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_employment_id', 'e_id');
    }
    
    /**
     * Get the user who created the employment type.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'e_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the employment type.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'e_updated_by', 'id');
    }
}