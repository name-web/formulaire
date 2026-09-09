<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'sequence' => 1,
            'number' => 'DT-2026-0001',
            'issued_at' => $this->faker->date(),
            'subject' => $this->faker->sentence(),
            'status' => Quote::STATUS_DRAFT,
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Quote $quote) {
            $quote->forceFill(['number' => sprintf('%s-%s-%04d', config('quote.number_prefix'), $quote->issued_at->format('Y'), $quote->sequence)])->save();
        });
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => Quote::STATUS_PAID]);
    }
}
