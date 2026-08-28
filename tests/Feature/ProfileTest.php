<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_route_does_not_exist(): void
    {
        $response = $this->get('/profile');
        $response->assertStatus(404);
    }
}
