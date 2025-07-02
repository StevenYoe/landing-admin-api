<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $connection = 'pazar';
    protected $table = 'pazar_company_profile';
    protected $primaryKey = 'cp_id';
    public $timestamps = false;

    protected $fillable = [
        'cp_description_id',
        'cp_description_en',
        'cp_type'
    ];
}