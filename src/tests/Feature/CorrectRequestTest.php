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

class CorrectRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後の時エラーメッセージ() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $newCheckIn = $attendance->check_out_time->copy()->addMinutes(30);

        $response = $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => $newCheckIn->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'remark' => 'test',
        ]);

        $response->assertSessionHasErrors(['check_in_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の時エラーメッセージ() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $newRestStart = $attendance->check_out_time->copy()->addMinutes(30);
        $newRestEnd = $newRestStart->copy()->addMinutes(30);

        $response = $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => $attendance->check_in_time->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'rests' => [
                'new' => [
                    'rest_start_time' => $newRestStart->format('H:i'),
                    'rest_end_time' => $newRestEnd->format('H:i'),
                ]
            ],
            'remark' => 'test',
        ]);

        $response->assertSessionHasErrors(['rests.new.rest_start_time' => '休憩時間が不適切な値です']);
    }

    /** @test */
    public function 休憩修了時間が退勤時間より後の時エラーメッセージ() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $newRestStart = $attendance->check_out_time->copy()->subMinutes(30);
        $newRestEnd = $newRestStart->copy()->addMinutes(30);

        $response = $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => $attendance->check_in_time->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'rests' => [
                'new' => [
                    'rest_start_time' => $newRestStart->format('H:i'),
                    'rest_end_time' => $newRestEnd->format('H:i'),
                ]
            ],
            'remark' => 'test',
        ]);

        $response->assertSessionHasErrors(['rests.new.rest_end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function 備考が未入力の時エラーメッセージ() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $response = $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => $attendance->check_in_time->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark' => '備考を記入してください']);
    }

    /** @test */
    public function 申請処理が実行される() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => '2026-04-01',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:30',
            'remark' => 'test',
        ]);

        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'remark' => 'test',
            'request_date' => Carbon::today(),
        ]);
        $this->assertDatabaseHas('attendance_corrections', [
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:30:00',
        ]);

        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)->first();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/approve/' . $correctionRequest->id);
        $response->assertStatus(200);

        $response->assertSee([
            $me->name,
            '2026年',
            '4月1日',
            '09:00',
            '18:30',
            'test',
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee([
            $me->name,
            '2026/04/01',
            'test',
            Carbon::today()->format('Y/m/d'),
        ]);
    }

    /** @test */
    public function 承認待ち一覧に全て表示される() {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $attendances = Attendance::factory(3)->create([
            'user_id' => $me->id,
        ]);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $other->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        foreach($attendances as $attendance){
            $this->post("/attendance/request", [
                'attendance_id' => $attendance->id,
                'check_in_time' => '09:00',
                'check_out_time' => '18:30',
                'remark' => 'test',
            ]);
        }

        $this->post("/attendance/request", [
            'attendance_id' => $otherAttendance->id,
            'check_in_time' => '10:00',
            'check_out_time' => '19:30',
            'remark' => 'dummy',
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee([
                $me->name,
                $attendance->work_date->format('Y/m/d'),
                'test',
                Carbon::today()->format('Y/m/d'),
            ]);
        }

        $response->assertDontSee([
            $other->name,
            'dummy',
        ]);
    }

    /** @test */
    public function 承認済み一覧に全て表示される() {
        $me = User::factory()->create();

        $attendances = Attendance::factory(3)->create([
            'user_id' => $me->id,
        ]);

        $dummyAttendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        foreach($attendances as $attendance){
            $this->post("/attendance/request", [
                'attendance_id' => $attendance->id,
                'check_in_time' => '09:00',
                'check_out_time' => '18:30',
                'remark' => 'test',
            ]);

            $correctionRequests[] = CorrectionRequest::where('attendance_id', $attendance->id)->first();
        }

        $this->post("/attendance/request", [
            'attendance_id' => $dummyAttendance->id,
            'check_in_time' => '10:00',
            'check_out_time' => '19:30',
            'remark' => 'dummy',
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        foreach($correctionRequests as $correctionRequest){
            $response = $this->post('/stamp_correction_request/approve/' . $correctionRequest->id);
        }

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/stamp_correction_request/list?page=complete');
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee([
                $me->name,
                $attendance->work_date->format('Y/m/d'),
                'test',
                Carbon::today()->format('Y/m/d'),
            ]);
        }

        $response->assertDontSee([
            'dummy',
        ]);
    }

    /** @test */
    public function 申請の詳細を押すと勤怠詳細画面に遷移する() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $this->post("/attendance/request", [
            'attendance_id' => $attendance->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:30',
            'remark' => 'test',
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee('/attendance/detail/' . $attendance->id);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee([
            $me->name,
            '2026年',
            '4月1日',
            '08:00',
            '19:30',
            '承認待ちのため修正はできません',
        ]);
    }
}
