<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外表示() {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中表示() {
        $me = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '出勤中',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中表示() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
            'check_out_time' => null,
            'status' => '休憩中',
        ]);

        $current = $attendance->check_in_time;
        $restStart = (clone $current)->addMinutes(rand(30, 120));

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start_time' => $restStart,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済表示() {
        $me = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');
    }
}
