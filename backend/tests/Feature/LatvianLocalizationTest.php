<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatvianLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_errors_are_latvian_even_for_a_russian_browser(): void
    {
        $this->withHeader('Accept-Language', 'ru-RU')->postJson('/api/auth/register', [
            'name' => '!', 'email' => 'invalid', 'password' => '1234', 'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Pārbaudi ievadītos datus.')
            ->assertJsonPath('errors.name.0', 'Lietotājvārdā izmanto 3–30 burtus vai ciparus.')
            ->assertJsonPath('errors.email.0', 'Ievadi derīgu e-pasta adresi.')
            ->assertJsonPath('errors.password.0', 'Laukā “parole” jābūt vismaz 5 rakstzīmēm.');
    }

    public function test_authentication_and_missing_resources_have_latvian_errors(): void
    {
        $this->getJson('/api/documents')->assertUnauthorized()->assertJsonPath('message', 'Lūdzu, pieslēdzies savam kontam.');
        $this->postJson('/api/auth/login', ['email' => 'missing@example.test', 'password' => 'invalid'])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Norādītais e-pasts vai parole nav pareiza.');
        $this->actingAs(User::factory()->create())->getJson('/api/documents/999999')
            ->assertNotFound()->assertJsonPath('message', 'Pieprasītais ieraksts nav atrasts.');
    }

    public function test_document_and_product_validation_use_latvian_field_names(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->postJson('/api/documents', [])
            ->assertUnprocessable()->assertJsonPath('errors.file.0', 'Lauks “fails” ir obligāts.');
        $this->postJson('/api/products', [
            'name' => 'Dators', 'category_id' => $category->id,
            'amount' => 100000, 'purchase_date' => '2026-10-23', 'warranty_end_date' => '2026-10-22',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.amount.0', 'Lauka “summa” vērtība nedrīkst pārsniegt 99999.99.')
            ->assertJsonPath('errors.warranty_end_date.0', 'Garantijas beigu datums nedrīkst būt pirms pirkuma datuma.');
        $this->getJson('/api/documents?date_from=2026-10-23&date_to=2026-10-22')
            ->assertUnprocessable()->assertJsonPath('errors.date_to.0', 'Lauka “pirkums līdz” datums nedrīkst būt pirms lauka “pirkums no” datuma.');
    }

    public function test_password_reset_token_errors_are_latvian(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email, 'token' => 'invalid', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123',
        ])->assertUnprocessable()->assertJsonPath('errors.email.0', 'Paroles atjaunošanas saite nav derīga vai tās termiņš ir beidzies.');
    }
}
