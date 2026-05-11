<?php

namespace App\Modules\Auth\Actions;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class GenerateOtpAction
{
    public function execute(User $user): void
    {
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

        Log::info('OTP: відправляю лист', [
            'user_id' => $user->id,
            'to'      => $user->email,
            'mailer'  => config('mail.default'),
            'host'    => config('mail.mailers.smtp.host'),
            'port'    => config('mail.mailers.smtp.port'),
            'from'    => config('mail.from.address'),
        ]);

        try {
            $sent = Mail::to($user->email)->send(new OtpMail($user, $code));

            $symfony = $sent !== null && method_exists($sent, 'getSymfonySentMessage')
                ? $sent->getSymfonySentMessage()
                : null;

            Log::info('OTP: SMTP відповідь Brevo', [
                'to'         => $user->email,
                'message_id' => $symfony?->getMessageId(),
                'debug'      => $symfony?->getDebug(),
            ]);
        } catch (TransportExceptionInterface $e) {
            Log::error('OTP: SMTP помилка від Brevo', [
                'to'      => $user->email,
                'message' => $e->getMessage(),
                'debug'   => $e->getDebug(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('OTP: непередбачувана помилка під час відправки', [
                'to'      => $user->email,
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}