<?php

namespace Tests\Feature\Services;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Shops;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function actingAsShop(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'shop']));
    }

    public function test_creates_a_pending_order_and_attaches_the_cart(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsShop();

        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $order = $this->service->createOrder($shop, 'Morning', 250.0, 'note', 'Tester');

        $this->assertSame('Pending', $order->status);
        $this->assertSame($shop->id, $order->shop_id);
        $this->assertDatabaseHas('carts', ['shop_bc_number' => $shop->branch_code, 'order_number' => $order->unique_id]);
    }

    public function test_rejects_a_zero_total(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsShop();
        $shop = Shops::factory()->create();

        $this->expectExceptionMessage('Order total price value must not be 0.00/=');
        $this->service->createOrder($shop, 'Morning', 0.0, null, 'Tester');
    }

    public function test_rejects_a_period_not_allowed_for_the_shop(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsShop();
        $shop = Shops::factory()->create(['order_time' => 'Evening']);

        $this->expectExceptionMessage('Invalid order time for this shop.');
        $this->service->createOrder($shop, 'Morning', 100.0, null, 'Tester');
    }

    public function test_rejects_outside_the_order_window(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(20, 0));
        $this->actingAsShop();
        $shop = Shops::factory()->create();

        $this->expectExceptionMessage('You can create orders between 8.00 a.m. to 4.00 p.m.');
        $this->service->createOrder($shop, 'Morning', 100.0, null, 'Tester');
    }

    public function test_rejects_a_second_order_for_the_same_period_same_day(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsShop();
        $shop = Shops::factory()->create();
        Orders::create(['unique_id' => uniqid(), 'shop' => $shop->branch_code, 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->expectExceptionMessage('Order already created for this time period in this shop.');
        $this->service->createOrder($shop, 'Morning', 100.0, null, 'Tester');
    }

    public function test_rejects_a_second_rapid_submission_from_the_same_actor(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsShop();
        $shop1 = Shops::factory()->create();
        $shop2 = Shops::factory()->create();

        $this->service->createOrder($shop1, 'Morning', 100.0, null, 'Tester');

        $this->expectExceptionMessage('Order Proceed button');
        $this->service->createOrder($shop2, 'Morning', 100.0, null, 'Tester');
    }

    public function test_creates_a_default_standing_order(): void
    {
        $this->actingAsShop();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $order = $this->service->createDefaultOrder($shop, 'Morning', 250.0, 'Weekly standing order', 'Tester');

        $this->assertSame('Default', $order->status);
        $this->assertSame('Default', $order->time_period);
        $this->assertSame('Morning', $order->default_period);
        $this->assertDatabaseHas('carts', ['order_number' => $order->unique_id, 'default_name' => 'Default']);
    }

    public function test_accepting_a_pending_order_moves_it_to_processing(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-ACCEPT', 'shop' => 'BC-1', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->service->transitionToProcessing('ORD-ACCEPT');

        $this->assertSame('Processing', $order->refresh()->status);
    }

    public function test_cancelling_an_order_sets_status_and_time_period_to_cancelled(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-CANCEL', 'shop' => 'BC-1', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->service->transitionToCancelled('ORD-CANCEL');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
    }

    public function test_transfer_moves_every_processing_order_to_complete_and_nothing_else(): void
    {
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-1', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Processing']);
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-2', 'total_price' => 10, 'time_period' => 'Evening', 'status' => 'Processing']);
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-3', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);

        $moved = $this->service->transferProcessingToComplete();

        $this->assertSame(2, $moved);
        $this->assertSame(0, Orders::where('status', 'Processing')->count());
        $this->assertSame(1, Orders::where('status', 'Pending')->count());
    }
}
