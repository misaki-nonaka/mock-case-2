<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_start_time',
        'rest_end_time',
    ];

    public function attendanceCorrection()
    {
        $this->hasOne('App\Models\AttendanceCorrection');
    }

    public function restCorrections()
    {
        $this->hasMany('App\Models\RestCorrection');
    }

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
