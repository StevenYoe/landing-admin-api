<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// ProductDetail model represents the 'pazar_product_detail' table in the 'pazar' database connection.
// It stores detailed information about products, including multilingual long descriptions, net weight, marketplace links, and user relationships.
class ProductDetail extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_product_detail';
    // The primary key for the table
    protected $primaryKey = 'pd_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'pd_created_at';
    const UPDATED_AT = 'pd_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'pd_id_product',        // Foreign key to the product
        'pd_net_weight',        // Net weight of the product
        'pd_longdesc_id',       // Long description in Indonesian
        'pd_longdesc_en',       // Long description in English
        'pd_link_shopee',       // Shopee marketplace link
        'pd_link_tokopedia',    // Tokopedia marketplace link
        'pd_link_blibli',       // Blibli marketplace link
        'pd_link_lazada',       // Lazada marketplace link
        'pd_created_by',        // User ID who created the product detail
        'pd_updated_by'         // User ID who last updated the product detail
    ];

    /**
     * Get the product that owns the detail.
     * Defines a relationship to the Product model.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'pd_id_product', 'p_id');
    }
    
    /**
     * Get the user who created the product detail.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'pd_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product detail.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'pd_updated_by', 'id');
    }
}