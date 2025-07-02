<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Department extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_departments';
    protected $primaryKey = 'da_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'da_created_at';
    const UPDATED_AT = 'da_updated_at';

    protected $fillable = [
        'da_title_id',
        'da_title_en',
        'da_created_by',
        'da_updated_by'
    ];

    /**
     * Get the vacancies for the department.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_department_id', 'da_id');
    }
    
    /**
     * Get the user who created the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'v_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'v_updated_by', 'id');
    }
}