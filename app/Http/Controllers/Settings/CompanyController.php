<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function edit(): Response
    {
        $company = auth()->user()->company;

        return Inertia::render('Settings/Company', [
            'company'  => $company,
            'logoUrl'  => $company?->logo_path
                ? Storage::url($company->logo_path)
                : null,
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $user    = auth()->user();
        $company = $user->company ?? $user->company()->create([
            'name'        => $request->name,
            'address'     => $request->address,
            'postal_code' => $request->postal_code,
            'city'        => $request->city,
            'country'     => $request->country,
            'email'       => $request->email,
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($data);

        return back()->with('success', __('settings.company_updated'));
    }

    public function deleteLogo(): RedirectResponse
    {
        $company = auth()->user()->company;

        if ($company && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return back()->with('success', __('settings.logo_deleted'));
    }
}
