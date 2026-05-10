<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $vat      = round($subtotal * 0.20, 2);

        return [
            'company_id' => Company::factory(),
            'client_id'  => Client::factory(),
            'number'     => 'INV-' . now()->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'     => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date'   => now()->addDays(30)->toDateString(),
            'subtotal'   => $subtotal,
            'vat_amount' => $vat,
            'total'      => $subtotal + $vat,
            'currency'   => 'EUR',
        ];
    }
}
