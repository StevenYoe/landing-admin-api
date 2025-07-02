<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Vacancy extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_vacancies';
    protected $primaryKey = 'v_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'v_created_at';
    const UPDATED_AT = 'v_updated_at';

    protected $fillable = [
        'v_title_id',
        'v_title_en',
        'v_department_id',
        'v_employment_id',
        'v_experience_id',
        'v_type',
        'v_description_id',
        'v_description_en',
        'v_requirement_id',
        'v_requirement_en',
        'v_responsibilities_id',
        'v_responsibilities_en',
        'v_posted_date',
        'v_closed_date',
        'v_urgent',
        'v_is_active',
        'v_created_by',
        'v_updated_by'
    ];

    protected $casts = [
        'v_urgent' => 'boolean',
        'v_is_active' => 'boolean',
        'v_posted_date' => 'date',
        'v_closed_date' => 'date'
    ];

    /**
     * Get the department that owns the vacancy.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'v_department_id', 'da_id');
    }

    /**
     * Get the employment type that owns the vacancy.
     */
    public function employment()
    {
        return $this->belongsTo(Employment::class, 'v_employment_id', 'e_id');
    }

    /**
     * Get the experience level that owns the vacancy.
     */
    public function experience()
    {
        return $this->belongsTo(Experience::class, 'v_experience_id', 'ex_id');
    }
    
    /**
     * Get the user who created the vacancy.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'v_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the vacancy.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'v_updated_by', 'id');
    }
}