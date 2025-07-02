<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// RecipeDetail model represents the 'pazar_recipe_detail' table in the 'pazar' database connection.
// It stores detailed information about recipes, including multilingual descriptions, ingredients, cooking steps, YouTube link, and user relationships.
class RecipeDetail extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_recipe_detail';
    // The primary key for the table
    protected $primaryKey = 'rd_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'rd_created_at';
    const UPDATED_AT = 'rd_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'rd_id_recipe',        // Foreign key to the recipe
        'rd_desc_id',          // Description in Indonesian
        'rd_desc_en',          // Description in English
        'rd_ingredients_id',   // Ingredients in Indonesian
        'rd_ingredients_en',   // Ingredients in English
        'rd_cook_id',          // Cooking steps in Indonesian
        'rd_cook_en',          // Cooking steps in English
        'rd_link_youtube',     // YouTube link for the recipe
        'rd_created_by',       // User ID who created the recipe detail
        'rd_updated_by'        // User ID who last updated the recipe detail
    ];

    /**
     * Get the recipe that owns the detail.
     * Defines a relationship to the Recipe model.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'rd_id_recipe', 'r_id');
    }
    
    /**
     * Get the user who created the recipe detail.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'rd_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the recipe detail.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'rd_updated_by', 'id');
    }
}