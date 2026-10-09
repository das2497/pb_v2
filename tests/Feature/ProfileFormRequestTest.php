<?php

namespace Tests\Feature;

use App\Models\Rep;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;



/**
 * Smoke tests for the role-specific profile update endpoints (Shop,
 * Rep, OrderAdmin) whose inline validate() calls were moved into
 * UpdateRoleProfileRequest / UpdateProfileDetailsRequest /
 * UpdatePasswordRequest. None had prior coverage.
 */
class ProfileFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_profile_update_succeeds_with_valid_data(): void
    {
        $user = User::factory()->create(['role' => 'shop']);
        Shops::factory()->create(['email' => $user->email]);
        $this->actingAs($user);

        $this->post('/shop/profile-update', [
            'name' => 'New Name',
            'email' => $user->email,
            'password' => 'newpassword',
            'confirm' => 'newpassword',
        ])->assertSessionHas('success');
    }

    public function test_rep_profile_update_succeeds_with_valid_data(): void
    {
        $user = User::factory()->create(['role' => 'rep']);
        Rep::factory()->create(['email' => $user->email]);
        $this->actingAs($user);

        $this->post('/rep/profile-update', [
            'name' => 'New Name',
            'email' => $user->email,
            'password' => 'newpassword',
            'confirm' => 'newpassword',
        ])->assertSessionHas('success');
    }

    public function test_order_admin_profile_details_update_succeeds_with_valid_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));

        $this->post('/order-admin/profile-update', [
            'name' => 'New Admin Name',
        ])->assertSessionHas('success');
    }

    public function test_order_admin_profile_password_update_succeeds_with_valid_data(): void
    {
        $user = User::factory()->create(['role' => 'o_admin', 'password' => bcrypt('current-password')]);
        $this->actingAs($user);

        $this->post('/order-admin/profile-update-password', [
            'current_password' => 'current-password',
            'new_password' => 'brand-new-password',
            'new_password_confirmation' => 'brand-new-password',
        ])->assertSessionHas('success');
    }
}
