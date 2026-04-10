<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタン正常機能() {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤');

        $response = $this->post('/attendance/register', [
            'attendance_register' => 'check-in',
        ]);

        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回() {
        $me = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $me->id,
            'work_date' => Carbon::now(),
        ]);

        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が一覧で確認できる() {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2026, 4, 1, 9, 0, 0));

        $response = $this->post('/attendance/register', [
            'attendance_register' => 'check-in',
        ]);

        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('09:00');
    }
}
