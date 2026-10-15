<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnicodeProfileTest extends TestCase
{
    use RefreshDatabase;


    public function test_username_length_counts_characters_and_still_rejects_spaces_and_punctuation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patchJson('/api/profile', ['name' => str_repeat('Ā', 30)])->assertOk();
        foreach ([str_repeat('Ā', 31), 'Āā', 'Jānis B', 'Jānis_1', 'Jānis!'] as $name) {
            $this->patchJson('/api/profile', ['name' => $name])->assertUnprocessable()->assertJsonValidationErrors('name');
        }
    }

    public function test_password_reset_email_is_localized_and_links_to_the_configured_frontend(): void
    {
        config(['app.frontend_url' => 'https://scan-save.example']);
        $user = User::factory()->create(['name' => 'Jānis123', 'email' => 'janis@example.test']);
        $mail = (new ResetPassword('example-token'))->toMail($user);
        $this->assertSame('Scan & Save: paroles atjaunošana', $mail->subject);
        $html = $mail->render();
        $this->assertStringContainsString('Atjaunot paroli', $html);
        $this->assertStringContainsString('Jānis123', $html);
        $this->assertStringContainsString('https://scan-save.example/reset-password?token=example-token&amp;email=janis%40example.test', $html);
        $this->assertStringContainsString('60 minūtes', $html);
    }

    public function test_reminder_preference_distinguishes_inherited_default_from_an_explicit_override(): void
    {
        SystemSetting::setValue('reminder_days', 30);
        $user = User::factory()->create(['reminder_days' => null]);
        $this->actingAs($user)->getJson('/api/profile')->assertOk()->assertJsonPath('user.reminder_days', 30)->assertJsonPath('user.reminder_days_override', null);
        $this->patchJson('/api/profile/preferences', ['reminder_days' => 14])->assertOk()->assertJsonPath('user.reminder_days', 14)->assertJsonPath('user.reminder_days_override', 14);
        SystemSetting::setValue('reminder_days', 60);
        $this->getJson('/api/profile')->assertOk()->assertJsonPath('user.reminder_days', 14)->assertJsonPath('user.reminder_days_override', 14);
        $this->patchJson('/api/profile/preferences', ['reminder_days' => null])->assertOk()->assertJsonPath('user.reminder_days', 60)->assertJsonPath('user.reminder_days_override', null);
    }
}
