<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'remark',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'date',
    ]; 

    public function attendanceCorrection()
    {
        return $this->hasOne('App\Models\AttendanceCorrection');
    }

    public function restCorrections()
    {
        return $this->hasMany('App\Models\RestCorrection');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function attendance()
    {
        return $this->belongsTo('App\Models\Attendance');
    }
}
