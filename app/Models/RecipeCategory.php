<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// RecipeCategory model represents the 'pazar_recipe_category' table in the 'pazar' database connection.
// It stores information about recipe categories, including multilingual titles, and defines relationships to recipes.
class RecipeCategory extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_recipe_category';
    // The primary key for the table
    protected $primaryKey = 'rc_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'rc_title_id', // Category title in Indonesian
        'rc_title_en'  // Category title in English
    ];

    /**
     * Get the recipes for the category.
     * Defines a many-to-many relationship with the Recipe model.
     */
    public function recipes()
    {
        return $this->belongsToMany(
            Recipe::class, 
            'pazar_recipe_category_junction', 
            'rcj_id_category', 
            'rcj_id_recipe'
        );
    }
}