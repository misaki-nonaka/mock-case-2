<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'check_in_time',
        'check_out_time',
    ];

    public function getCheckInTimeFormattedAttribute()
    {
        return $this->check_in_time
            ? Carbon::parse($this->check_in_time)->format('H:i')
            : null;
    }

    public function getCheckOutTimeFormattedAttribute()
    {
        return $this->check_out_time
            ? Carbon::parse($this->check_out_time)->format('H:i')
            : null;
    }
}
