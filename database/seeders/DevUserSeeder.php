<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => env('DEV_USER_EMAIL', 'dev@invoiceapp.test')],
            [
                'name'     => 'Dev User',
                'locale'   => 'fr',
                'password' => Hash::make('password'),
            ]
        );

        if (! $user->company) {
            Company::create([
                'user_id'     => $user->id,
                'name'        => 'Ma Société SARL',
                'address'     => '12 rue de la Paix',
                'postal_code' => '75001',
                'city'        => 'Paris',
                'country'     => 'France',
                'email'       => 'contact@masociete.fr',
                'phone'       => '+33 1 23 45 67 89',
                'vat_number'  => 'FR12345678901',
            ]);
        }
    }
}
