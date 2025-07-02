<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Vacancy model represents the 'career_vacancies' table in the 'pazar' database connection.
// It stores information about job vacancies, including multilingual titles, descriptions, requirements, responsibilities, department, employment, experience, posting/closing dates, urgency, active status, and user relationships.
class Vacancy extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_vacancies';
    // The primary key for the table
    protected $primaryKey = 'v_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'v_created_at';
    const UPDATED_AT = 'v_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'v_title_id',              // Vacancy title in Indonesian
        'v_title_en',              // Vacancy title in English
        'v_department_id',         // Foreign key to department
        'v_employment_id',         // Foreign key to employment type
        'v_experience_id',         // Foreign key to experience level
        'v_type',                  // Type of vacancy
        'v_description_id',        // Description in Indonesian
        'v_description_en',        // Description in English
        'v_requirement_id',        // Requirements in Indonesian
        'v_requirement_en',        // Requirements in English
        'v_responsibilities_id',   // Responsibilities in Indonesian
        'v_responsibilities_en',   // Responsibilities in English
        'v_posted_date',           // Date the vacancy is posted
        'v_closed_date',           // Date the vacancy is closed
        'v_urgent',                // Boolean indicating if the vacancy is urgent
        'v_is_active',             // Boolean indicating if the vacancy is active
        'v_created_by',            // User ID who created the vacancy
        'v_updated_by'             // User ID who last updated the vacancy
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'v_urgent' => 'boolean',         // Ensure 'v_urgent' is always treated as a boolean
        'v_is_active' => 'boolean',      // Ensure 'v_is_active' is always treated as a boolean
        'v_posted_date' => 'date',       // Cast 'v_posted_date' to a date
        'v_closed_date' => 'date'        // Cast 'v_closed_date' to a date
    ];

    /**
     * Get the department that owns the vacancy.
     * Defines a relationship to the Department model.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'v_department_id', 'da_id');
    }

    /**
     * Get the employment type that owns the vacancy.
     * Defines a relationship to the Employment model.
     */
    public function employment()
    {
        return $this->belongsTo(Employment::class, 'v_employment_id', 'e_id');
    }

    /**
     * Get the experience level that owns the vacancy.
     * Defines a relationship to the Experience model.
     */
    public function experience()
    {
        return $this->belongsTo(Experience::class, 'v_experience_id', 'ex_id');
    }
    
    /**
     * Get the user who created the vacancy.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'v_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the vacancy.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'v_updated_by', 'id');
    }
}