<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteItem>
 */
class QuoteItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->numberBetween(1000, 50000);

        return [
            'quote_id' => Quote::factory(),
            'designation' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
            'sort_order' => 0,
        ];
    }
}
