<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $quantity  = $this->faker->randomFloat(2, 1, 10);
        $vatRate   = 20.00;
        $ht        = round($unitPrice * $quantity, 2);
        $ttc       = round($ht * 1.20, 2);

        return [
            'invoice_id'  => Invoice::factory(),
            'product_id'  => null,
            'description' => $this->faker->sentence(3),
            'unit_price'  => $unitPrice,
            'unit'        => 'unité',
            'quantity'    => $quantity,
            'vat_rate'    => $vatRate,
            'total_ht'    => $ht,
            'total_ttc'   => $ttc,
            'sort_order'  => 0,
        ];
    }
}
