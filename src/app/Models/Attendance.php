<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'check_in_time',
        'check_out_time',
        'status',
    ];

    public function rests()
    {
        return $this->hasMany('App\Models\Rest');
    }

    public function request()
    {
        return $this->hasOne('App\Models\Request');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function totalRestMinutes(){
        return $this->rests->sum(function($rest){
            if(!$rest->rest_end_time){
                return 0;
            }

            return Carbon::parse($rest->rest_start_time)
                ->diffInMinutes(Carbon::parse($rest->rest_end_time));
        });
    }

    public function totalRestTime(){
        $restMinutes = $this->totalRestMinutes();

        $hours = floor($restMinutes / 60);
        $mins = $restMinutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function workTime() {
        if(!$this->check_out_time){
            return null;
        }

        $workMinutes = Carbon::parse($this->check_in_time)
            ->diffInMinutes(Carbon::parse($this->check_out_time));

        $workMinutes -= $this->totalRestMinutes();

        $hours = floor($workMinutes / 60);
        $mins = $workMinutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

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
