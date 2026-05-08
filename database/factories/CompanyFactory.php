<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'name'         => $this->faker->company(),
            'logo_path'    => null,
            'address'      => $this->faker->streetAddress(),
            'postal_code'  => $this->faker->postcode(),
            'city'         => $this->faker->city(),
            'country'      => $this->faker->country(),
            'email'        => $this->faker->companyEmail(),
            'phone'        => $this->faker->phoneNumber(),
            'vat_number'   => null,
            'iban'         => null,
            'legal_footer' => null,
        ];
    }
}
