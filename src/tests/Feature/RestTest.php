<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class RestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩ボタン正常機能() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩入');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日複数回可能() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response = $this->get('/attendance');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-end',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタン正常機能() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-end',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日複数回可能() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response = $this->get('/attendance');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-end',
        ]);

        $response = $this->get('/attendance');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時間が一覧で確認できる() {
        $me = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $me->id,
            'work_date' => '2026-04-01',
            'check_in_time' => '09:00:00',
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2026, 4, 1, 11, 0, 0));

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-start',
        ]);

        $response = $this->get('/attendance');

        Carbon::setTestNow(Carbon::create(2026, 4, 1, 12, 30, 0));

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'rest-end',
        ]);

        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('1:30');
    }
}
