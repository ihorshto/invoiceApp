<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Modules\Auth\Actions\VerifyOtpAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OtpController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (! session('otp_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/VerifyOtp');
    }

    public function store(VerifyOtpRequest $request, VerifyOtpAction $action): RedirectResponse
    {
        $userId = session('otp_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $action->execute($user, $request->code);

        Auth::login($user);
        session()->forget('otp_user_id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
