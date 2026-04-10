<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'rest_id',
        'rest_start_time',
        'rest_end_time',
    ];

    protected $casts = [
        'rest_start_time' => 'datetime',
        'rest_end_time' => 'datetime',
    ];

    public function getRestStartFormattedAttribute()
    {
        return $this->rest_start_time
            ? \Carbon\Carbon::parse($this->rest_start_time)->format('H:i')
            : null;
    }

    public function getRestEndFormattedAttribute()
    {
        return $this->rest_end_time
            ? \Carbon\Carbon::parse($this->rest_end_time)->format('H:i')
            : null;
    }
}
