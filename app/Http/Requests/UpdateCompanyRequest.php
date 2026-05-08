<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['required', 'string', 'max:1000'],
            'postal_code'  => ['required', 'string', 'max:20'],
            'city'         => ['required', 'string', 'max:255'],
            'country'      => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'vat_number'   => ['nullable', 'string', 'max:50'],
            'iban'         => ['nullable', 'string', 'max:50'],
            'legal_footer' => ['nullable', 'string', 'max:2000'],
            'logo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}
