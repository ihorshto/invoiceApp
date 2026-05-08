<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendOtpEmailJob;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OtpLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_valid_credentials_dispatch_otp_and_redirect_to_verify(): void
    {
        Queue::fake();
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('login.verify'));

        Queue::assertPushed(SendOtpEmailJob::class);
        $this->assertDatabaseHas('login_otps', ['user_id' => $user->id]);
        $this->assertEquals($user->id, session('otp_user_id'));
    }

    public function test_invalid_credentials_fail_with_error(): void
    {
        Queue::fake();
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        Queue::assertNothingPushed();
    }

    public function test_verify_page_requires_session(): void
    {
        $this->get(route('login.verify'))
            ->assertRedirect(route('login'));
    }

    public function test_correct_otp_logs_in_user(): void
    {
        Queue::fake();
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $code = '123456';

        LoginOtp::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts'   => 0,
            'created_at' => now(),
        ]);

        session(['otp_user_id' => $user->id]);

        $this->post(route('login.verify'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('login_otps', ['user_id' => $user->id, 'used_at' => now()->toDateTimeString()]);
    }

    public function test_wrong_otp_increments_attempts(): void
    {
        $user = User::factory()->create();
        $otp = LoginOtp::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'attempts'   => 0,
            'created_at' => now(),
        ]);

        session(['otp_user_id' => $user->id]);

        $this->post(route('login.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertEquals(1, $otp->fresh()->attempts);
        $this->assertGuest();
    }

    public function test_expired_otp_fails(): void
    {
        $user = User::factory()->create();
        LoginOtp::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
            'attempts'   => 0,
            'created_at' => now(),
        ]);

        session(['otp_user_id' => $user->id]);

        $this->post(route('login.verify'), ['code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_logout_destroys_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
