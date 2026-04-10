<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示される() {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->assertAuthenticated();

        $attendances = collect([
            ['date' => '2026-04-01', 'time' => '09:01:00'],
            ['date' => '2026-04-02', 'time' => '09:02:00'],
            ['date' => '2026-04-03', 'time' => '09:03:00'],
        ])->map(function ($item) use ($me) {

            $start = \Carbon\Carbon::parse($item['date'] . ' ' . $item['time']);

            return Attendance::factory()->create([
                'user_id' => $me->id,
                'work_date' => $item['date'],
                'check_in_time' => $start,
                'check_out_time' => (clone $start)->addHours(8),
            ]);
        });

        $otherAttendances = collect([
            ['date' => '2026-04-01', 'time' => '10:01:00'],
            ['date' => '2026-04-02', 'time' => '10:02:00'],
            ['date' => '2026-04-03', 'time' => '10:03:00'],
        ])->map(function ($item) use ($other) {

            $start = \Carbon\Carbon::parse($item['date'] . ' ' . $item['time']);

            return Attendance::factory()->create([
                'user_id' => $other->id,
                'work_date' => $item['date'],
                'check_in_time' => $start,
                'check_out_time' => (clone $start)->addHours(8),
            ]);
        });

        $response = $this->get("/attendance/list?date=2026-04");

        foreach($attendances as $attendance){
            $response->assertSee($attendance->check_in_time->format('H:i'));
            $response->assertSee($attendance->check_out_time->format('H:i'));
        }

        foreach($otherAttendances as $otherAttendance){
            $response->assertDontSee($otherAttendance->check_in_time->format('H:i'));
            $response->assertDontSee($otherAttendance->check_out_time->format('H:i'));
        }
    }

    /** @test */
    public function 遷移した際に現在の月が表示される() {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $now = Carbon::today();

        $response = $this->get("/attendance/list");
        $response->assertStatus(200);

        $response->assertSee($now->format('Y/m'));
    }

    /** @test */
    public function 前月の勤怠一覧が表示される() {
        $me = User::factory()->create();
        $prevMonth = Carbon::today()->copy()->subMonthNoOverflow()->format('Y-m');

        $dates = collect(range(1, 5))->map(function ($day) use ($prevMonth) {
            return Carbon::createFromFormat('Y-m-d', "$prevMonth-$day")->toDateString();
        });

        $attendances = $dates->map(function ($date) use ($me) {
            return Attendance::factory()->create([
                'user_id' => $me->id,
                'work_date' => $date,
            ]);
        });

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/list");
        $response->assertStatus(200);

        $response = $this->get(route('attendance.list', ['date' => $prevMonth] ));
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee($attendance->check_in_time->format('H:i'));
            $response->assertSee($attendance->check_out_time->format('H:i'));
        }
    }

    /** @test */
    public function 翌月の勤怠一覧が表示される() {
        $me = User::factory()->create();
        $nextMonth = Carbon::today()->copy()->addMonthNoOverflow()->format('Y-m');

        $dates = collect(range(1, 5))->map(function ($day) use ($nextMonth) {
            return Carbon::createFromFormat('Y-m-d', "$nextMonth-$day")->toDateString();
        });

        $attendances = $dates->map(function ($date) use ($me) {
            return Attendance::factory()->create([
                'user_id' => $me->id,
                'work_date' => $date,
            ]);
        });

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/list");
        $response->assertStatus(200);

        $response = $this->get(route('attendance.list', ['date' => $nextMonth] ));
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee($attendance->check_in_time->format('H:i'));
            $response->assertSee($attendance->check_out_time->format('H:i'));
        }
    }

    /** @test */
    public function 勤怠詳細画面に遷移する() {
        $me = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::today(),
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get("/attendance/list");
        $response->assertStatus(200);

        $response = $this->get("/attendance/detail/" . $attendance->id);
        $response->assertStatus(200);
    }
}
