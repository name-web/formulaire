<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientComment>
 */
class ClientCommentFactory extends Factory
{
    /** @use Factory<ClientComment> */
    protected $model = ClientComment::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->sentence(),
        ];
    }
}
