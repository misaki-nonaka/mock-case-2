<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function アドレス未記入のバリデーションメッセージ() {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワード未記入のバリデーションメッセージ() {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function 誤入力のバリデーションメッセージ() {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        $response->assertSessionHasErrors(['password' => 'ログイン情報が登録されていません']);
    }
}
