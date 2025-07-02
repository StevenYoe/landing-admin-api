<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_certifications';
    protected $primaryKey = 'c_id';
    public $timestamps = false;

    protected $fillable = [
        'c_label_id',
        'c_label_en',
        'c_title_id',
        'c_title_en',
        'c_description_id',
        'c_description_en',
        'c_image'
    ];
}