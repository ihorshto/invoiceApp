<?php

namespace App\Modules\Auth\Actions;

use App\Jobs\SendOtpEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateOtpAction
{
    public function execute(User $user): void
    {
        // Invalidate all previous unused OTPs
        $user->loginOtps()
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->loginOtps()->create([
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('auth.otp_expires_minutes', 10)),
            'attempts'   => 0,
            'created_at' => now(),
        ]);

        SendOtpEmailJob::dispatch($user, $code);
    }
}
