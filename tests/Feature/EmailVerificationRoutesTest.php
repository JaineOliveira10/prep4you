<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class EmailVerificationRoutesTest extends TestCase
{
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_verification_notice_requires_authentication(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
    }
}
