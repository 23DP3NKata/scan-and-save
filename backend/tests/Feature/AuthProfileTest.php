<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_hashes_password_and_starts_a_session_without_elevated_role(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Alice123', 'email' => 'alice@example.test',
            'password' => 'safePass123', 'password_confirmation' => 'safePass123', 'role' => 1, 'status' => 0,
        ])->assertCreated()->assertJsonPath('user.role', 0)->assertJsonMissingPath('user.password');

        $user = User::where('email', 'alice@example.test')->sole();
        $this->assertTrue(Hash::check('safePass123', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);
    }

    public function test_registration_validates_documented_limits_and_unique_email(): void
    {
        User::factory()->create(['email' => 'alice@example.test']);
        $this->postJson('/api/auth/register', [
            'name' => 'Bad Name!', 'email' => 'alice@example.test', 'password' => '1234', 'password_confirmation' => '1234',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);
        $this->postJson('/api/auth/register', [
            'name' => 'Al', 'email' => str_repeat('x', 25).'@example.test', 'password' => '12345', 'password_confirmation' => '12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_login_logout_and_bad_credentials(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.test', 'password' => 'safePass123']);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'incorrect'])->assertUnprocessable();
        $this->assertGuest();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'safePass123'])->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->postJson('/api/auth/logout')->assertNoContent();
        $this->assertGuest();
        $this->getJson('/api/documents')->assertUnauthorized();
    }

    public function test_blocked_users_cannot_login_or_continue_an_existing_session(): void
    {
        $user = User::factory()->create(['status' => 0, 'password' => 'safePass123']);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'safePass123'])->assertForbidden();
        $this->actingAs($user)->getJson('/api/documents')->assertForbidden();
        $this->assertGuest();
    }

    public function test_repeated_bad_logins_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])->assertUnprocessable();
        }
        $this->postJson('/api/auth/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_password_reset_revokes_existing_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create(['email' => 'alice@example.test', 'password' => 'originalPass']);
        DB::table('sessions')->insert(['id' => 'existing-session', 'user_id' => $user->id, 'payload' => base64_encode(serialize([])), 'last_activity' => time()]);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'originalPass'])->assertOk();
        $token = Password::createToken($user);
        $this->postJson('/api/auth/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'replacementPass', 'password_confirmation' => 'replacementPass'])->assertOk();
        $this->assertDatabaseMissing('sessions', ['id' => 'existing-session']);
        $this->assertGuest();
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_password_reset_uses_one_time_token_and_returns_generic_response_for_unknown_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'alice@example.test', 'password' => 'originalPass']);
        $knownResponse = $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk()->json();
        $this->postJson('/api/auth/forgot-password', ['email' => 'missing@example.test'])->assertOk()->assertExactJson($knownResponse);
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });
        $payload = ['email' => $user->email, 'token' => $token, 'password' => 'replacedPass123', 'password_confirmation' => 'replacedPass123'];
        $this->postJson('/api/auth/reset-password', $payload)->assertOk();
        $this->assertTrue(Hash::check('replacedPass123', $user->fresh()->password));
        $this->postJson('/api/auth/reset-password', $payload)->assertUnprocessable();
    }
}
