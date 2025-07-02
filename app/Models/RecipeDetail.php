<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RecipeDetail extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_recipe_detail';
    protected $primaryKey = 'rd_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'rd_created_at';
    const UPDATED_AT = 'rd_updated_at';

    protected $fillable = [
        'rd_id_recipe',
        'rd_desc_id',
        'rd_desc_en',
        'rd_ingredients_id',
        'rd_ingredients_en',
        'rd_cook_id',
        'rd_cook_en',
        'rd_link_youtube',
        'rd_created_by',
        'rd_updated_by'
    ];

    /**
     * Get the recipe that owns the detail.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'rd_id_recipe', 'r_id');
    }
    
    /**
     * Get the user who created the recipe detail.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'rd_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the recipe detail.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'rd_updated_by', 'id');
    }
}