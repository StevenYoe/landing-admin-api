<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAtPazar extends Model
{
    protected $connection = 'pazar';
    protected $table = 'career_work_at_pazar';
    protected $primaryKey = 'wap_id';
    public $timestamps = false;

    protected $fillable = [
        'wap_title_id',
        'wap_title_en',
        'wap_description_id',
        'wap_description_en',
        'wap_type'
    ];
}