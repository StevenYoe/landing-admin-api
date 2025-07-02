<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Recipe extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_recipe';
    protected $primaryKey = 'r_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'r_created_at';
    const UPDATED_AT = 'r_updated_at';

    protected $fillable = [
        'r_title_id',
        'r_title_en',
        'r_image',
        'r_created_by',
        'r_updated_by',
        'r_is_active'
    ];

    protected $casts = [
        'r_is_active' => 'boolean'
    ];

    /**
     * Get the categories for this recipe.
     */
    public function categories()
    {
        return $this->belongsToMany(
            RecipeCategory::class, 
            'pazar_recipe_category_junction', 
            'rcj_id_recipe', 
            'rcj_id_category'
        );
    }

    /**
     * Get the detail associated with the recipe.
     */
    public function detail()
    {
        return $this->hasOne(RecipeDetail::class, 'rd_id_recipe', 'r_id');
    }
    
    /**
     * Get the user who created the recipe.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'r_created_by', 'id');
    }

    /**
     * Get the user who last updated the recipe.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'r_updated_by', 'id');
    }
}