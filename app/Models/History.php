<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_history';
    protected $primaryKey = 'hs_id';
    public $timestamps = false;

    protected $fillable = [
        'hs_year',
        'hs_description_id',
        'hs_description_en',
        'hs_image'
    ];
}