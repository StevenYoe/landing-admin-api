<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ProductCategory model represents the 'pazar_product_category' table in the 'pazar' database connection.
// It stores information about product categories, including multilingual titles, descriptions, and an image.
class ProductCategory extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_product_category';
    // The primary key for the table
    protected $primaryKey = 'pc_id';
    // Disable automatic timestamps
    public $timestamps = false;

    // The attributes that are mass assignable
    protected $fillable = [
        'pc_title_id',        // Category title in Indonesian
        'pc_title_en',        // Category title in English
        'pc_description_id',  // Description in Indonesian
        'pc_description_en',  // Description in English
        'pc_image'            // Image path or filename
    ];

    /**
     * Get the products for the category.
     * Defines a one-to-many relationship with the Product model.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'p_id_product_category', 'pc_id');
    }
}