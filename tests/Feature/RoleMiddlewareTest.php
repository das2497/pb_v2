<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the current role-gating behavior (Login,
 * OrderAdmin, Rep, Shop middleware) before the Phase 2 refactor replaces
 * the 5 duplicate middleware classes with a single parameterized one.
 * These tests lock in *current* behavior, including its quirks (the 4
 * equivalent admin role strings), so the refactor can be verified against
 * them rather than against assumptions.
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_page_at_root(): void
    {
        $this->get('/')->assertOk()->assertViewIs('login');
    }

    public function test_guest_is_redirected_to_login_from_a_protected_area(): void
    {
        $this->get('/order-admin/dashboard')->assertRedirect('/');
        $this->get('/rep/dashboard')->assertRedirect('/');
        $this->get('/shop/dashboard')->assertRedirect('/');
    }

    public function test_order_admin_can_reach_order_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);

        $this->actingAs($admin)->get('/order-admin/dashboard')->assertOk();
    }

    /** @dataProvider equivalentAdminRoleStrings */
    public function test_each_equivalent_admin_role_string_grants_order_admin_access(string $role): void
    {
        $admin = User::factory()->create(['role' => $role]);

        $this->actingAs($admin)->get('/order-admin/dashboard')->assertOk();
    }

    public static function equivalentAdminRoleStrings(): array
    {
        return [
            ['o_admin'],
            ['admin'],
            ['view'],
            ['sales_admin'],
        ];
    }

    public function test_rep_cannot_reach_order_admin_dashboard_and_is_bounced_to_their_own(): void
    {
        $rep = User::factory()->create(['role' => 'rep']);

        $this->actingAs($rep)->get('/order-admin/dashboard')->assertRedirect('/rep/dashboard');
    }

    public function test_shop_cannot_reach_rep_dashboard_and_is_bounced_to_their_own(): void
    {
        $shop = User::factory()->create(['role' => 'shop']);

        $this->actingAs($shop)->get('/rep/dashboard')->assertRedirect('/shop/dashboard');
    }

    public function test_order_admin_cannot_reach_shop_dashboard_and_is_bounced_to_their_own(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);

        $this->actingAs($admin)->get('/shop/dashboard')->assertRedirect('/order-admin/dashboard');
    }

    public function test_an_unrecognized_role_is_sent_to_404(): void
    {
        $user = User::factory()->create(['role' => 'something_unknown']);

        $this->actingAs($user)->get('/order-admin/dashboard')->assertRedirect('/404');
    }
}
