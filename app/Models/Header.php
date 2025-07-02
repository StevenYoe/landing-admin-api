<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Header extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_headers';
    protected $primaryKey = 'h_id';
    public $timestamps = false;

    protected $fillable = [
        'h_title_id',
        'h_title_en',
        'h_page_name',
        'h_description_id',
        'h_description_en',
        'h_image'
    ];
}