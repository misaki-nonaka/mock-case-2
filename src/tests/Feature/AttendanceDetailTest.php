<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 名前が正しく表示される() {
        $me = User::factory()->create([
            'name' => '佐藤太郎'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee("佐藤太郎");
    }

    /** @test */
    public function 日付が正しく表示される() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => '2026-04-01',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee("2026年");
        $response->assertSee("4月1日");
    }

    /** @test */
    public function 出勤退勤時刻が正しく表示される() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:30:00',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee("09:00");
        $response->assertSee("18:30");
    }

    /** @test */
    public function 休憩時刻が正しく表示される() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:00:00',
        ]);

        $attendance->rests()->create([
            'rest_start_time' => '12:00:00',
            'rest_end_time' => '13:30:00',
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee("12:00");
        $response->assertSee("13:30");
    }
}
