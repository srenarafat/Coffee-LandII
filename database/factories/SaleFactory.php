<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'invoice_no' => $this->faker->unique()->numerify('INV-####'),
        ];
    }
}