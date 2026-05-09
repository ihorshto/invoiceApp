<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id'  => Company::factory(),
            'name'        => $this->faker->company(),
            'email'       => $this->faker->unique()->companyEmail(),
            'phone'       => $this->faker->phoneNumber(),
            'address'     => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'city'        => $this->faker->city(),
            'country'     => $this->faker->country(),
            'vat_number'  => null,
            'notes'       => null,
        ];
    }
}
