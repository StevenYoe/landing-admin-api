<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// Product model represents the 'pazar_product' table in the 'pazar' database connection.
// It stores information about products, including multilingual titles, descriptions, category, image, creator, updater, and active status.
class Product extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_product';
    // The primary key for the table
    protected $primaryKey = 'p_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'p_created_at';
    const UPDATED_AT = 'p_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'p_title_id',              // Product title in Indonesian
        'p_title_en',              // Product title in English
        'p_id_product_category',   // Foreign key to product category
        'p_description_id',        // Description in Indonesian
        'p_description_en',        // Description in English
        'p_image',                 // Image path or filename
        'p_created_by',            // User ID who created the product
        'p_updated_by',            // User ID who last updated the product
        'p_is_active'              // Boolean indicating if the product is active
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'p_is_active' => 'boolean' // Ensure 'p_is_active' is always treated as a boolean
    ];

    /**
     * Get the category that owns the product.
     * Defines a relationship to the ProductCategory model.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'p_id_product_category', 'pc_id');
    }

    /**
     * Get the product detail associated with the product.
     * Defines a one-to-one relationship with the ProductDetail model.
     */
    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'pd_id_product', 'p_id');
    }
    
    /**
     * Get the user who created the product.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'p_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'p_updated_by', 'id');
    }
}