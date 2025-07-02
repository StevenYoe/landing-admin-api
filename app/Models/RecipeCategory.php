<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeCategory extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_recipe_category';
    protected $primaryKey = 'rc_id';
    public $timestamps = false;

    protected $fillable = [
        'rc_title_id',
        'rc_title_en'
    ];

    /**
     * Get the recipes for the category.
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