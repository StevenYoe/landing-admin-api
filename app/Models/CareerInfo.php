<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerInfo extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_careerinfo';
    protected $primaryKey = 'ci_id';
    public $timestamps = false;

    protected $fillable = [
        'ci_title_id',
        'ci_title_en',
        'ci_description_id',
        'ci_description_en',
        'ci_image'
    ];
}