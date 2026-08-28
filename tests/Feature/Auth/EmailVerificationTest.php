<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_routes_do_not_exist(): void
    {
        $response = $this->get('/verify-email');
        $response->assertStatus(404);
    }
}
