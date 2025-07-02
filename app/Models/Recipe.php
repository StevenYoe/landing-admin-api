<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Recipe model represents the 'pazar_recipe' table in the 'pazar' database connection.
// It stores information about recipes, including multilingual titles, image, creator, updater, active status, and relationships to categories and details.
class Recipe extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_recipe';
    // The primary key for the table
    protected $primaryKey = 'r_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'r_created_at';
    const UPDATED_AT = 'r_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'r_title_id',      // Recipe title in Indonesian
        'r_title_en',      // Recipe title in English
        'r_image',         // Image path or filename
        'r_created_by',    // User ID who created the recipe
        'r_updated_by',    // User ID who last updated the recipe
        'r_is_active'      // Boolean indicating if the recipe is active
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'r_is_active' => 'boolean' // Ensure 'r_is_active' is always treated as a boolean
    ];

    /**
     * Get the categories for this recipe.
     * Defines a many-to-many relationship with the RecipeCategory model.
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
     * Defines a one-to-one relationship with the RecipeDetail model.
     */
    public function detail()
    {
        return $this->hasOne(RecipeDetail::class, 'rd_id_recipe', 'r_id');
    }
    
    /**
     * Get the user who created the recipe.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'r_created_by', 'id');
    }

    /**
     * Get the user who last updated the recipe.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'r_updated_by', 'id');
    }
}