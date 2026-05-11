<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_inertia_shares_user_locale_from_database(): void
    {
        $user = User::factory()->create(['locale' => 'uk']);
        Company::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('locale', 'uk'));
    }
}
