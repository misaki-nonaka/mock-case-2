<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\Admin;

class AdminCorrectionTest extends TestCase
{
    /** @test */
    public function 承認待ち一覧に全て表示される() {
        $users = User::factory(3)->create();

        foreach($users as $user){
            $attendances[] = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        foreach($attendances as $attendance){
            $correctionRequests[] = CorrectionRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'status' => '承認待ち',
                'remark' => 'test',
                'request_date' => today(),
            ]);
        }
        foreach($correctionRequests as $correctionRequest){
            $correctionRequest->attendanceCorrection()->create([
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
            ]);
        }

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee([
                $attendance->user->name,
                $attendance->work_date->format('Y/m/d'),
                'test',
                Carbon::today()->format('Y/m/d'),
            ]);
        }
    }

    /** @test */
    public function 承認済み一覧に全て表示される() {
        $users = User::factory(3)->create();

        foreach($users as $user){
            $attendances[] = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        foreach($attendances as $attendance){
            $correctionRequests[] = CorrectionRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'status' => '承認済み',
                'remark' => 'test',
                'request_date' => today(),
            ]);
        }
        foreach($correctionRequests as $correctionRequest){
            $correctionRequest->attendanceCorrection()->create([
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
            ]);
        }

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/list?page=complete');
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee([
                $attendance->user->name,
                $attendance->work_date->format('Y/m/d'),
                'test',
                Carbon::today()->format('Y/m/d'),
            ]);
        }
    }

    /** @test */
    public function 修正申請の詳細が正しく表示される() {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $this->actingAs($user)->assertAuthenticated();

        $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:30',
            'remark' => 'test',
        ]);

        $correction = CorrectionRequest::where('attendance_id', $attendance->id)->first();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/approve/' . $correction->id);
        $response->assertStatus(200);

        $response->assertSee([
            $user->name,
            '2026年',
            '4月1日',
            '09:00',
            '18:30',
        ]);
    }

    /** @test */
    public function 修正申請の承認が実行される() {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $this->actingAs($user)->assertAuthenticated();

        $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:30',
            'remark' => 'test',
        ]);

        $correction = CorrectionRequest::where('attendance_id', $attendance->id)->first();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->post('/stamp_correction_request/approve/' . $correction->id);

        $this->assertDatabaseHas('correction_requests', [
            'status' => '承認済み',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:30:00',
            'remark' => 'test',
        ]);
    }
}
