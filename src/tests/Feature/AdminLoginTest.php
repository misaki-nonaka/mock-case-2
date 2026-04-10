<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\AdminSeeder;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function アドレス未記入のバリデーションメッセージ() {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $this->seed(AdminSeeder::class);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワード未記入のバリデーションメッセージ() {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $this->seed(AdminSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin1@sample.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function 誤入力のバリデーションメッセージ() {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $this->seed(AdminSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin1@sample.com',
            'password' => '12345678',
        ]);

        $response->assertSessionHasErrors(['password' => 'ログイン情報が登録されていません']);
    }
}
