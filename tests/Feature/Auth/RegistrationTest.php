<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_does_not_exist(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }
}
