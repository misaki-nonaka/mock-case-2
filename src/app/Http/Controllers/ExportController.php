<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function export($user_id, $date){
        $start = Carbon::parse($date)->copy()->startOfMonth();
        $end = Carbon::parse($date)->copy()->endOfMonth();

        $attendances = Attendance::with('rests')->where('user_id', $user_id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $csvData = [];
        
        foreach($attendances as $attendance) {
            $rests = $attendance->rests;

            if($rests->isNotEmpty()) {
                foreach($rests as $index => $rest) {
                    if($index == 0) {
                        $csvData[] = [
                            $attendance->work_date->format('Y/m/d'),
                            Carbon::parse($attendance->check_in_time)?->format('H:i'),
                            Carbon::parse($attendance->check_out_time)?->format('H:i'),
                            Carbon::parse($rest->rest_start_time)?->format('H:i'),
                            Carbon::parse($rest->rest_end_time)?->format('H:i'),
                            $attendance->remark,
                        ];
                    }
                    else {
                        $csvData[] = [
                            '',
                            '',
                            '',
                            Carbon::parse($rest->rest_start_time)?->format('H:i'),
                            Carbon::parse($rest->rest_end_time)?->format('H:i'),
                            '',
                        ];
                    }
                }
            }
            else {
                $csvData[] = [
                    $attendance->work_date->format('Y/m/d'),
                    Carbon::parse($attendance->check_in_time)?->format('H:i'),
                    Carbon::parse($attendance->check_out_time)?->format('H:i'),
                    '',
                    '',
                    $attendance->remark,
                ];
            }
        }

        $response = new StreamedResponse(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            $heading = ['work_date', 'check_in_time', 'check_out_time', 'rest_start_time', 'rest_end_time', 'remark'];
            mb_convert_variables('SJIS-win', 'UTF-8', $heading);
            fputcsv($handle, $heading);

            foreach ($csvData as $row) {
                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($handle, $row);
            }
            fclose($handle);
        });

        $filename = 'attendance_user' . $attendance->user_id . '_' . Carbon::parse($date)->format('Y-m') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            "attachment; filename={$filename}"
        );

        return $response;
    }
}
