<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProductDetail extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_product_detail';
    protected $primaryKey = 'pd_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'pd_created_at';
    const UPDATED_AT = 'pd_updated_at';

    protected $fillable = [
        'pd_id_product',
        'pd_net_weight',
        'pd_longdesc_id',
        'pd_longdesc_en',
        'pd_link_shopee',
        'pd_link_tokopedia',
        'pd_link_blibli',
        'pd_link_lazada',
        'pd_created_by',
        'pd_updated_by'
    ];

    /**
     * Get the product that owns the detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'pd_id_product', 'p_id');
    }
    
    /**
     * Get the user who created the product detail.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'pd_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product detail.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'pd_updated_by', 'id');
    }
}