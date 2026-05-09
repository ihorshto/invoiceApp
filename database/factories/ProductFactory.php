<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id'  => Company::factory(),
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit_price'  => $this->faker->randomFloat(2, 10, 500),
            'unit'        => $this->faker->randomElement(['unité', 'heure', 'jour', 'kg']),
            'vat_rate'    => $this->faker->randomElement([0, 5.5, 10, 20]),
            'is_active'   => true,
        ];
    }
}
