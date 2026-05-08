<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('mail.otp_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8f8f8; padding: 40px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,.08);">
        <h2 style="color: #1e40af; margin-top: 0;">{{ __('mail.otp_title') }}</h2>
        <p>{{ __('mail.otp_greeting', ['name' => $user->name]) }}</p>
        <p>{{ __('mail.otp_body') }}</p>
        <div style="text-align: center; margin: 32px 0;">
            <span style="font-size: 40px; font-weight: bold; letter-spacing: 12px; color: #1e40af; background: #eff6ff; padding: 16px 24px; border-radius: 8px;">
                {{ $code }}
            </span>
        </div>
        <p style="color: #64748b; font-size: 14px;">
            {{ __('mail.otp_expires', ['minutes' => $expiresMinutes]) }}
        </p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="color: #94a3b8; font-size: 12px;">{{ __('mail.otp_ignore') }}</p>
    </div>
</body>
</html>
