<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタン正常機能() {
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

        $response->assertSee('退勤');

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'check-out',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が一覧で確認できる() {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2026, 4, 1, 9, 0, 0));

        $response = $this->post('/attendance/register', [
            'attendance_register' => 'check-in',
        ]);

        $attendance = Attendance::where('user_id', $me->id)->where('work_date', '2026-04-01')->first();

        $response = $this->get('/attendance');

        Carbon::setTestNow(Carbon::create(2026, 4, 1, 18, 0, 0));

        $response = $this->post('/attendance/register', [
            'attendance_id' => $attendance->id,
            'attendance_register' => 'check-out',
        ]);

        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('18:00');
    }
}
