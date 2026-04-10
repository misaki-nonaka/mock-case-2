<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Admin;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 選択したデータが正しく表示される() {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $other->id,
            'work_date' => '2020-10-01',
            'check_in_time' => '09:00',
            'check_out_time' => '18:30',
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee([
            $user->name,
            '2026年',
            '4月1日',
            '08:00',
            '19:30',
        ]);

        $response->assertDontSee([
            $other->name,
            '2020年',
            '10月1日',
            '09:00',
            '18:30',
        ]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後の時エラーメッセージ() {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $newCheckIn = $attendance->check_out_time->copy()->addMinutes(30);

        $response = $this->post('/admin/attendance/correction', [
            'attendance_id' => $attendance->id,
            'check_in_time' => $newCheckIn->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'remark' => 'test',
        ]);

        $response->assertSessionHasErrors(['check_in_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の時エラーメッセージ() {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $newRestStart = $attendance->check_out_time->copy()->addMinutes(30);
        $newRestEnd = $newRestStart->copy()->addMinutes(30);

        $response = $this->post('/admin/attendance/correction', [
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
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $newRestStart = $attendance->check_out_time->copy()->subMinutes(30);
        $newRestEnd = $newRestStart->copy()->addMinutes(30);

        $response = $this->post('/admin/attendance/correction', [
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
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/admin/attendance/correction', [
            'attendance_id' => $attendance->id,
            'check_in_time' => $attendance->check_in_time->format('H:i'),
            'check_out_time' => $attendance->check_out_time->format('H:i'),
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark' => '備考を記入してください']);
    }
}
