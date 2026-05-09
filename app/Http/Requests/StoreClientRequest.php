<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'address'     => ['required', 'string', 'max:1000'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city'        => ['required', 'string', 'max:255'],
            'country'     => ['required', 'string', 'max:255'],
            'vat_number'  => ['nullable', 'string', 'max:50'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }
}