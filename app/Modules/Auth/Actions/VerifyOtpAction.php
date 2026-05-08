<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerifyOtpAction
{
    public function execute(User $user, string $code): void
    {
        $blockKey = "otp_blocked:{$user->id}";

        if (Cache::has($blockKey)) {
            throw ValidationException::withMessages([
                'code' => __('auth.otp_blocked'),
            ]);
        }

        $otp = $user->loginOtps()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => __('auth.otp_expired'),
            ]);
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');

            $maxAttempts = (int) config('auth.otp_max_attempts', 3);

            if ($otp->attempts >= $maxAttempts) {
                Cache::put(
                    $blockKey,
                    true,
                    now()->addMinutes((int) config('auth.otp_block_minutes', 15))
                );

                throw ValidationException::withMessages([
                    'code' => __('auth.otp_blocked'),
                ]);
            }

            throw ValidationException::withMessages([
                'code' => __('auth.otp_invalid'),
            ]);
        }

        $otp->update(['used_at' => now()]);
    }
}
