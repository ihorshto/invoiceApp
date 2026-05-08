<?php

namespace Tests\Feature\Settings;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_settings_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.company'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Settings/Company'));
    }

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get(route('settings.company'))
            ->assertRedirect(route('login'));
    }

    public function test_company_is_created_on_first_save(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('settings.company'), [
                'name'        => 'Acme Corp',
                'address'     => '123 Rue de la Paix',
                'postal_code' => '75001',
                'city'        => 'Paris',
                'country'     => 'France',
                'email'       => 'contact@acme.fr',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'user_id' => $user->id,
            'name'    => 'Acme Corp',
            'city'    => 'Paris',
        ]);
    }

    public function test_company_is_updated_on_subsequent_save(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $this->actingAs($user)
            ->post(route('settings.company'), [
                'name'        => 'New Name',
                'address'     => $company->address,
                'postal_code' => $company->postal_code,
                'city'        => $company->city,
                'country'     => $company->country,
                'email'       => $company->email,
            ])
            ->assertRedirect();

        $this->assertEquals('New Name', $company->fresh()->name);
    }

    public function test_logo_is_uploaded_and_stored(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Company::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('settings.company'), [
                'name'        => 'Acme',
                'address'     => '123 rue',
                'postal_code' => '75001',
                'city'        => 'Paris',
                'country'     => 'France',
                'email'       => 'a@a.com',
                'logo'        => UploadedFile::fake()->image('logo.png', 200, 200),
            ]);

        Storage::disk('public')->assertExists(
            'logos/' . basename($user->fresh()->company->logo_path)
        );
    }

    public function test_required_fields_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('settings.company'), [])
            ->assertSessionHasErrors(['name', 'address', 'postal_code', 'city', 'country', 'email']);
    }
}
