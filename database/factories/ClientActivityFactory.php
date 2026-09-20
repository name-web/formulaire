<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientActivity>
 */
class ClientActivityFactory extends Factory
{
    /** @use Factory<ClientActivity> */
    protected $model = ClientActivity::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory()->commercial(),
            'type' => ClientActivity::TYPE_VIEWED,
            'description' => 'Consultation de la fiche prospect.',
            'meta' => [],
        ];
    }

    public function ofType(string $type, ?string $description = null): static
    {
        return $this->state(fn () => [
            'type' => $type,
            'description' => $description ?? ClientActivity::typeConfig($type)['label'],
        ]);
    }

    public function by(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->getKey()]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn () => ['client_id' => $client->getKey()]);
    }
}
