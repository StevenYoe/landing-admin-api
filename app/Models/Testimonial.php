<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_testimonial';
    protected $primaryKey = 't_id';
    public $timestamps = false;

    protected $fillable = [
        't_name',
        't_description_id',
        't_description_en',
        't_type',
        't_gender',
        't_image'
    ];
}