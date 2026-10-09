<?php

namespace Tests\Feature\Rep;

use App\Models\Rep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test for Rep\CompleteOrders::index()'s date-filter validation,
 * moved into CompleteOrdersDateFilterRequest. No prior coverage.
 */
class CompleteOrdersFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_with_a_date_succeeds(): void
    {
        $rep = Rep::factory()->create();
        $this->actingAs(User::factory()->create(['email' => $rep->email, 'role' => 'rep']));

        $this->get('/rep/complete-order?search=ORD&date=2026-01-01')
            ->assertOk();
    }
}
