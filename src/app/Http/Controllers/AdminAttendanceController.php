<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\RequestRegisterRequest;

class AdminAttendanceController extends Controller
{
    public function list(Request $request){
        $dateString = $request->input('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($dateString);

        $attendances = Attendance::with('rests')->where('work_date', $currentDate)->get();

        $prevDay = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin.list', compact('attendances', 'currentDate', 'prevDay', 'nextDay'));
    }

    public function detail($attendance_id){
        $requestAvailable = !CorrectionRequest::where('attendance_id', $attendance_id)->exists();
        
        if($requestAvailable == false){
            $correction = CorrectionRequest::where('attendance_id', $attendance_id)->first();
            $requestStatus = $correction->status == '承認待ち' ? 'waiting' : 'stamp';
        }
        else{
            $requestStatus = null;
        }

        $attendance = Attendance::with(['user', 'rests'])->findOrFail($attendance_id);

        return view('detail', compact('attendance', 'requestAvailable', 'requestStatus'));
    }

    public function update(RequestRegisterRequest $request){
        $attendance = Attendance::with('rests')->find($request->attendance_id);
        $attendance->update($request->only(['check_in_time', 'check_out_time', 'remark']));

        $rests = $request->rests ?? [];
        foreach($rests as $restId => $restData) {
            if (empty($restData['rest_start_time']) && empty($restData['rest_end_time'])) {
                continue;
            }

            $data = [
                'rest_start_time' => $restData['rest_start_time'],
                'rest_end_time'   => $restData['rest_end_time'],
            ];

            if ($restId === 'new') {
                $attendance->rests()->create($data);
            }
            else {
                $attendance->rests()
                    ->where('id', $restId)
                    ->update($data);
            }
        }

        return redirect()->route('admin.detail', $attendance->id);
    }

    public function staff(){
        $users = User::all();

        return view('admin.staff', compact('users'));
    }

    public function individual(Request $request, $user_id){
        $dateString = $request->input('date', now()->format('Y-m'));
        $currentDate = Carbon::parse($dateString);

        $start = $currentDate->copy()->startOfMonth();
        $end = $currentDate->copy()->endOfMonth();

        $user = User::find($user_id);
        $attendances = Attendance::with('rests')->where('user_id', $user_id)
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        $period = CarbonPeriod::create($start, $end);

        $prevMonth = $currentDate->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonthNoOverflow()->format('Y-m');

        return view('admin.individual', compact('user', 'attendances', 'currentDate', 'prevMonth', 'nextMonth', 'period'));
    }
}
