<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'Ken',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'Ken',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    public function test_password_min_length()
    {
        $response = $this->post('/register', [
            'name' => 'Ken',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    public function test_password_must_be_confirmed()
    {
        $response = $this->post('/register', [
            'name' => 'Ken',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);
        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    public function test_user_is_saved_to_database()
    {
        $user = User::factory()->create([
            'name' => 'Ken',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Ken',
            'email' => 'test@example.com',
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'Ken',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect('/email/verify');
    }
}
