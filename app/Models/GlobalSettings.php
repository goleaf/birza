<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSettings extends Model
{
    protected $table = 'global_settings';

    protected $fillable = [
        'portal_additional_price',
    ];

    public $timestamps = false;

}
