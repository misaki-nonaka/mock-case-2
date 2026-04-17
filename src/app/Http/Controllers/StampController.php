<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionRequest;

class StampController extends Controller
{
    public function approve($correction_request_id){
        $correction = CorrectionRequest::with('attendanceCorrection', 'restCorrections', 'user', 'attendance')->findOrFail($correction_request_id);

        return view('admin.request-detail', compact('correction'));
    }

    public function stamp($correction_request_id){
        DB::transaction(function () use ($correction_request_id) {
            $correction = CorrectionRequest::with('attendanceCorrection', 'restCorrections')
                ->findOrFail($correction_request_id);
            $correction->update([
                'status' => "承認済み"
            ]);

            $attendanceCorrection = $correction->attendanceCorrection;

            $attendance = $correction->attendance;

            $attendance->update([
                'check_in_time' => $attendanceCorrection->check_in_time,
                'check_out_time'   => $attendanceCorrection->check_out_time,
                'remark' => $correction->remark,
            ]);

            foreach ($correction->restCorrections as $restCorrection) {
                if ($restCorrection->rest_id) {
                    $rest = $attendance->rests()->find($restCorrection->rest_id);
                    $rest->update([
                        'rest_start_time' => $restCorrection->rest_start_time,
                        'rest_end_time'   => $restCorrection->rest_end_time,
                    ]);
                }
                else {
                    $attendance->rests()->create([
                        'rest_start_time' => $restCorrection->rest_start_time,
                        'rest_end_time' => $restCorrection->rest_end_time,
                    ]);
                }
            }
        });

        return redirect()->route('admin.approve', $correction_request_id);
    }
}
