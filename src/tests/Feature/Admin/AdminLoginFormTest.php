<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminLoginFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_required_for_admin()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    public function test_password_is_required_for_admin()
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    public function test_login_fails_with_incorrect_password_for_admin()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct_password'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(302);
        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません。', $errors->first('email'));

        $this->assertGuest();
    }

    public function test_login_with_valid_data_for_admin()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => 1,
        ]);
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect('/admin/attendance/list');

        $this->assertAuthenticatedAs($admin);
    }
}
