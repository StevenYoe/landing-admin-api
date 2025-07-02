<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyPazar extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_why_pazar';
    protected $primaryKey = 'w_id';
    public $timestamps = false;

    protected $fillable = [
        'w_title_id',
        'w_title_en',
        'w_description_id',
        'w_description_en',
        'w_image'
    ];
}