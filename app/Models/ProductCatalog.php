<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProductCatalog extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_product_catalog';
    protected $primaryKey = 'pct_id';
    
    // Define which columns are used for created/updated timestamps
    const CREATED_AT = 'pct_created_at';
    const UPDATED_AT = 'pct_updated_at';

    protected $fillable = [
        'pct_catalog_id',
        'pct_catalog_en',
        'pct_created_by',
        'pct_updated_by'
    ];

    /**
     * Get the user who created the product catalog.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'pct_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product catalog.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'pct_updated_by', 'id');
    }
}