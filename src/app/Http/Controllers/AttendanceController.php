<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function attendance(){
        $userId = Auth::id();
        $attendance = Attendance::where('user_id', $userId)->where('work_date', today())->first();

        return view('attendance', compact('attendance'));
    }

    public function register(Request $request){
        $userId = Auth::id();
        $attendanceId = $request->attendance_id;
        if($request->attendance_register == "check-in"){
            Attendance::create([
                'user_id' => $userId,
                'work_date' => today(),
                'check_in_time' => now(),
                'status' => "出勤中",
            ]);
        }
        elseif($request->attendance_register == "check-out"){
            Attendance::findOrFail($attendanceId)->update([
                'check_out_time' => now(),
                'status' => "退勤済",
            ]);
        }
        elseif($request->attendance_register == "rest-start"){
            Attendance::findOrFail($attendanceId)->update([
                'status' => "休憩中",
            ]);
            Rest::create([
                'attendance_id' => $attendanceId,
                'rest_start_time' => now(),
            ]);
        }
        elseif($request->attendance_register == "rest-end"){
            Attendance::find($attendanceId)->update([
                'status' => "出勤中",
            ]);
            Rest::where('attendance_id', $attendanceId)
            ->whereNull('rest_end_time')            ->latest()->first()->update([
                'rest_end_time' => now(),
            ]);
        }

        return redirect('/attendance');
    }

    public function list(Request $request){
        $userId = Auth::id();
        $dateString = $request->input('date', now()->format('Y-m'));
        $currentDate = Carbon::parse($dateString);

        $start = $currentDate->copy()->startOfMonth();
        $end = $currentDate->copy()->endOfMonth();

        $attendances = Attendance::with('rests')->where('user_id', $userId)
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy('work_date');

        $period = CarbonPeriod::create($start, $end);

        $prevMonth = $currentDate->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonthNoOverflow()->format('Y-m');

        return view('list', compact('attendances', 'currentDate', 'prevMonth', 'nextMonth', 'period'));
    }

    public function detail($attendance_id){
        $requestAvailable = !CorrectionRequest::where('attendance_id', $attendance_id)->exists();

        if($requestAvailable){
            $attendance = Attendance::with(['user', 'rests'])->findOrFail($attendance_id);
        }
        else{
            $attendance = CorrectionRequest::with('user', 'attendanceCorrection', 'restCorrections')->where('attendance_id', $attendance_id)->first();
        }

        return view('detail', compact('attendance', 'requestAvailable'));
    }
}
