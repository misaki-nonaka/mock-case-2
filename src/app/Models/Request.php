<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'remark',
        'request_date',
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
