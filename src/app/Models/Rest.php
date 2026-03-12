<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
