<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'rest_id',
        'rest_start_time',
        'rest_end_time',
    ];
}
