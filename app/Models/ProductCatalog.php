<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

// ProductCatalog model represents the 'pazar_product_catalog' table in the 'pazar' database connection.
// It stores information about product catalogs, including multilingual catalog names and user relationships.
class ProductCatalog extends Model
{
    // Use the 'pazar' database connection for this model
    protected $connection = 'pazar';
    // The table associated with the model
    protected $table = 'pazar_product_catalog';
    // The primary key for the table
    protected $primaryKey = 'pct_id';
    
    // Define which columns are used for created/updated timestamps
    // By default, Eloquent expects 'created_at' and 'updated_at', but here we use custom column names
    const CREATED_AT = 'pct_created_at';
    const UPDATED_AT = 'pct_updated_at';

    // The attributes that are mass assignable
    protected $fillable = [
        'pct_catalog_id',    // Catalog name in Indonesian
        'pct_catalog_en',    // Catalog name in English
        'pct_created_by',    // User ID who created the catalog
        'pct_updated_by'     // User ID who last updated the catalog
    ];

    /**
     * Get the user who created the product catalog.
     * Defines a relationship to the User model.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'pct_created_by', 'id');
    }
    
    /**
     * Get the user who last updated the product catalog.
     * Defines a relationship to the User model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'pct_updated_by', 'id');
    }
}