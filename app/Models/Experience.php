<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Experience extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_experiences';
    protected $primaryKey = 'ex_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'ex_created_at';
    const UPDATED_AT = 'ex_updated_at';

    protected $fillable = [
        'ex_title_id',
        'ex_title_en',
        'ex_created_by',
        'ex_updated_by'
    ];

    /**
     * Get the vacancies for the experience level.
     */
    public function vacancies()
    {
        return $this->hasMany(Vacancy::class, 'v_experience_id', 'ex_id');
    }
    
    /**
     * Get the user who created the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'ex_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'ex_updated_by', 'id');
    }
}