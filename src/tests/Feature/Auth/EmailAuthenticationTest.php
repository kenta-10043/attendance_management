<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_verify_email(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'ken',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/email/verify');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user) {
            $verificationUrl = $notification->toMail($user)->actionUrl;

            $response = $this->actingAs($user)->get($verificationUrl);
            $response->assertRedirect('/attendance');

            $user->refresh();
            $this->assertNotNull($user->email_verified_at);

            $this->assertAuthenticatedAs($user);

            return true;
        });
    }
}
