<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_footer';
    protected $primaryKey = 'f_id';
    public $timestamps = false;

    protected $fillable = [
        'f_type',
        'f_label_id',
        'f_label_en',
        'f_description_id',
        'f_description_en',
        'f_icon',
        'f_link'
    ];
}