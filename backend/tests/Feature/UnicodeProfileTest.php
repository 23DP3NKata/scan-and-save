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
}
