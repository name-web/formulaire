<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'status' => 'prospect',
            'phone' => $this->faker->phoneNumber(),
        ];
    }

    public function client(): static
    {
        return $this->state(fn () => ['status' => 'client']);
    }

    public function assignedTo(User $developer): static
    {
        return $this->state(fn () => ['assigned_developer_id' => $developer->getKey()]);
    }
}
