<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Employment extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_employments';
    protected $primaryKey = 'e_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'e_created_at';
    const UPDATED_AT = 'e_updated_at';

    protected $fillable = [
        'e_title_id',
        'e_title_en',
        'e_created_by',
        'e_updated_by'
    ];

    /**
     * Get the vacancies for the employment type.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_employment_id', 'e_id');
    }
    
    /**
     * Get the user who created the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'e_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'e_updated_by', 'id');
    }
}