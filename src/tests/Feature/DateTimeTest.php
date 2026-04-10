<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在日時取得()
    {
        $me = User::factory()->create();
        $this->actingAs($me)->assertAuthenticated();

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $date = Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)');
        $time = Carbon::now()->format('H:i');

        $response->assertSee($date);
        $response->assertSee($time);
    }
}
