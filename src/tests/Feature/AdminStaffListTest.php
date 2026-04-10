<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Admin;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 全一般ユーザーの氏名とアドレスが表示される() {
        $users = User::factory(3)->create();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        foreach($users as $user){
            $response->assertSee([
                $user->name,
                $user->email,
            ]);
        }
    }

    /** @test */
    public function 選択したユーザーの勤怠が表示される() {
        $user = User::factory()->create();
        $others = User::factory(2)->create();

        $attendances = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('/admin/attendance/staff/' . $user->id);

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response->assertSee([
            $user->name,
            Carbon::today()->format('Y/m'),
            '08:00',
            '19:30'
        ]);
    }

    /** @test */
    public function 前月の勤怠一覧が表示される() {
        $user = User::factory()->create();
        $prevMonth = Carbon::today()->copy()->subMonthNoOverflow()->format('Y-m');

        $dates = collect(range(1, 5))->map(function ($day) use ($prevMonth) {
            return Carbon::createFromFormat('Y-m-d', "$prevMonth-$day")->toDateString();
        });

        $attendances = $dates->map(function ($date) use ($user) {
            return Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
        });

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response = $this->get(route('admin.individual', ['id' => $user->id, 'date' => $prevMonth] ));
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee($attendance->check_in_time->format('H:i'));
            $response->assertSee($attendance->check_out_time->format('H:i'));
        }
    }

    /** @test */
    public function 翌月の勤怠一覧が表示される() {
        $user = User::factory()->create();
        $nextMonth = Carbon::today()->copy()->addMonthNoOverflow()->format('Y-m');

        $dates = collect(range(1, 5))->map(function ($day) use ($nextMonth) {
            return Carbon::createFromFormat('Y-m-d', "$nextMonth-$day")->toDateString();
        });

        $attendances = $dates->map(function ($date) use ($user) {
            return Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
        });

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response = $this->get(route('admin.individual', ['id' => $user->id, 'date' => $nextMonth] ));
        $response->assertStatus(200);

        foreach($attendances as $attendance){
            $response->assertSee($attendance->check_in_time->format('H:i'));
            $response->assertSee($attendance->check_out_time->format('H:i'));
        }
    }

    /** @test */
    public function 勤怠詳細画面に遷移する() {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'check_in_time' => '08:00',
            'check_out_time' => '19:30',
        ]);

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response->assertSee('/admin/attendance/' . $attendance->id);

        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);
    }
}
