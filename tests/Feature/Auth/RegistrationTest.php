<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Post-hotfix #1: role can no longer be set from the request (it's
     * hardcoded to 'shop'), closing the privilege-escalation hole where
     * posting role=o_admin used to silently create an admin account.
     * Auth::login() is still commented out in the controller (pre-existing,
     * unrelated to the hotfix), so registering still doesn't log the user
     * in — this locks in that remaining quirk rather than assuming it away.
     */
    public function test_new_users_can_register_with_the_shop_role_regardless_of_input(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'o_admin',
        ]);

        $response->assertRedirect('/register');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'shop',
        ]);
    }
}
