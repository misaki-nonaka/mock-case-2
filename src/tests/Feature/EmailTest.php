<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後認証メール送信() {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'サンプル太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /** @test */
    public function メール認証サイトに遷移() {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->get('/email/verify');
        $response->assertStatus(200);

        $response->assertSee('http://localhost:8025');
        // 外部リンクのため、viewにリンクがあるかのみ確認しています
    }

    /** @test */
    public function 認証完了すると勤怠登録画面に遷移() {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $this->assertFalse($user->hasVerifiedEmail());

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirectContains('/attendance');
    }
}
