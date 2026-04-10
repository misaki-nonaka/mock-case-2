<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use App\Http\Requests\RequestRegisterRequest;

class RequestController extends Controller
{
    public function register(RequestRegisterRequest $request){
        $attendance = Attendance::find($request->attendance_id);
        $correctionRequest = CorrectionRequest::create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'remark' => $request->remark,
            'request_date' => today(),
        ]);
        $correctionRequest->attendanceCorrection()->create([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
        ]);

        $rests = $request->rests ?? [];
        foreach($rests as $restId => $restData) {

            if (empty($restData['rest_start_time']) && empty($restData['rest_end_time'])) {
                continue;
            }

            $data = [
                'rest_start_time' => $restData['rest_start_time'],
                'rest_end_time' => $restData['rest_end_time'],
            ];

            if ($restId !== 'new') {
                $data['rest_id'] = $restId;
            }

            $correctionRequest->restCorrections()->create($data);
        }
        
        return redirect()->route('detail', $attendance->id);
    }

    public function list(Request $request){
        $userId = Auth::id();
        $activeTab = $request->query('page', 'waiting');

        if (auth('admin')->check()) {
            if($request->page == 'complete'){
                $correctionRequests = CorrectionRequest::where('status', '承認済み')->get();
            }
            else{
                $correctionRequests = CorrectionRequest::where('status', '承認待ち')->get();
            }
        }

        else{
            if($request->page == 'complete'){
                $correctionRequests = CorrectionRequest::where('user_id', $userId)->where('status', '承認済み')->get();
            }
            else{
                $correctionRequests = CorrectionRequest::where('user_id', $userId)->where('status', '承認待ち')->get();
            }
        }

        return view('request-list', compact('activeTab', 'correctionRequests'));
    }
}
