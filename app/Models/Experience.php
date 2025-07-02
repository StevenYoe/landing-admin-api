<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Experience model represents the 'career_experiences' table in the 'pazar' database connection.
// It stores information about experience levels and their relationships to users and vacancies.
class Experience extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'career_experiences';
    // The primary key for the table
    protected $primaryKey = 'ex_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'ex_created_at';
    const UPDATED_AT = 'ex_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'ex_title_id',      // Experience level title in Indonesian
        'ex_title_en',      // Experience level title in English
        'ex_created_by',    // User ID who created the experience level
        'ex_updated_by'     // User ID who last updated the experience level
    ];

    /**
     * Get the vacancies for the experience level.
     * Defines a one-to-many relationship with the Vacancy model.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_experience_id', 'ex_id');
    }
    
    /**
     * Get the user who created the experience level.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'ex_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the experience level.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'ex_updated_by', 'id');
    }
}