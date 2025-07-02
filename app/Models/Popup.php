<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_popup';
    protected $primaryKey = 'pu_id';
    public $timestamps = false;

    protected $fillable = [
        'pu_image',
        'pu_link',
        'pu_is_active'
    ];

    protected $casts = [
        'pu_is_active' => 'boolean'
    ];
}