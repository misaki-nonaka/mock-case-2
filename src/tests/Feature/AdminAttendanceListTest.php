<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Admin;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日の全ユーザーの勤怠情報表示() {
        $users = User::factory(3)->create();
        $others = User::factory(3)->create();

        foreach($users as $user){
            $todayAttendances[] = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::today(),
            ]);
        }

        foreach($others as $other){
            $yesterdayAttendances[] = Attendance::factory()->create([
                'user_id' => $other->id,
                'work_date' => Carbon::yesterday(),
            ]);
        }

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        
        $response->assertSee([
            today()->isoformat('YYYY年M月D日'),
            today()->format('Y/m/d'),
        ]);

        foreach($todayAttendances as $todayAttendance){
            $response->assertSee([
                $todayAttendance->check_in_time->format('H:i'),
                $todayAttendance->check_out_time->format('H:i'),
            ]);
        }

        foreach($users as $user){
            $response->assertSee($user->name);
        }

        foreach($others as $other){
            $response->assertDontSee($other->name);
        }
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される() {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSee([
            today()->isoformat('YYYY年M月D日'),
            today()->format('Y/m/d'),
        ]);
    }

    /** @test */
    public function 前日の勤怠一覧が表示される() {
        $users = User::factory(3)->create();
        $others = User::factory(3)->create();

        foreach($users as $user){
            $yesterdayAttendances[] = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::yesterday(),
            ]);
        }

        foreach($others as $other){
            $todayAttendances[] = Attendance::factory()->create([
                'user_id' => $other->id,
                'work_date' => Carbon::today(),
            ]);
        }

        $prevDay = Carbon::today()->copy()->subDay()->format('Y-m-d');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response = $this->get(route('admin.attendance.list', ['date' => $prevDay] ));
        $response->assertStatus(200);

        $response->assertSee([
            Carbon::yesterday()->isoformat('YYYY年M月D日'),
            Carbon::yesterday()->format('Y/m/d'),
        ]);

        foreach($yesterdayAttendances as $yesterdayAttendance){
            $response->assertSee($yesterdayAttendance->check_in_time->format('H:i'));
            $response->assertSee($yesterdayAttendance->check_out_time->format('H:i'));
        }

        foreach($users as $user){
            $response->assertSee($user->name);
        }

        foreach($others as $other){
            $response->assertDontSee($other->name);
        }
    }

    /** @test */
    public function 翌日の勤怠一覧が表示される() {
        $users = User::factory(3)->create();
        $others = User::factory(3)->create();

        foreach($users as $user){
            $tomorrowAttendances[] = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::tomorrow(),
            ]);
        }

        foreach($others as $other){
            $todayAttendances[] = Attendance::factory()->create([
                'user_id' => $other->id,
                'work_date' => Carbon::today(),
            ]);
        }

        $nextDay = Carbon::today()->copy()->addDay()->format('Y-m-d');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response = $this->get(route('admin.attendance.list', ['date' => $nextDay] ));
        $response->assertStatus(200);

        $response->assertSee([
            Carbon::tomorrow()->isoformat('YYYY年M月D日'),
            Carbon::tomorrow()->format('Y/m/d'),
        ]);

        foreach($tomorrowAttendances as $tomorrowAttendance){
            $response->assertSee($tomorrowAttendance->check_in_time->format('H:i'));
            $response->assertSee($tomorrowAttendance->check_out_time->format('H:i'));
        }

        foreach($users as $user){
            $response->assertSee($user->name);
        }

        foreach($others as $other){
            $response->assertDontSee($other->name);
        }
    }
}
