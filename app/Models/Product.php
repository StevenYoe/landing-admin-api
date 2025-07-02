<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Product extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_product';
    protected $primaryKey = 'p_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'p_created_at';
    const UPDATED_AT = 'p_updated_at';

    protected $fillable = [
        'p_title_id',
        'p_title_en',
        'p_id_product_category',
        'p_description_id',
        'p_description_en',
        'p_image',
        'p_created_by',
        'p_updated_by',
        'p_is_active'
    ];

    protected $casts = [
        'p_is_active' => 'boolean'
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'p_id_product_category', 'pc_id');
    }

    /**
     * Get the product detail associated with the product.
     */
    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'pd_id_product', 'p_id');
    }
    
    /**
     * Get the user who created the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'p_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'p_updated_by', 'id');
    }
}