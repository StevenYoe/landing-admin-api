<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Department model represents the 'career_departments' table in the 'pazar' database connection.
// It stores information about company departments and their relationships to users and vacancies.
class Department extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_departments';
    // The primary key for the table
    protected $primaryKey = 'da_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'da_created_at';
    const UPDATED_AT = 'da_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'da_title_id',      // Department title in Indonesian
        'da_title_en',      // Department title in English
        'da_created_by',    // User ID who created the department
        'da_updated_by'     // User ID who last updated the department
    ];

    /**
     * Get the vacancies for the department.
     * Defines a one-to-many relationship with the Vacancy model.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_department_id', 'da_id');
    }
    
    /**
     * Get the user who created the department.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'v_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the department.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'v_updated_by', 'id');
    }
}