<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Shops;
use App\Services\Support\PreventsDuplicateSubmission;
use Carbon\Carbon;

/**
 * Centralizes order creation and status transitions, which were
 * previously reimplemented with small, inconsistent variations across
 * OrderAdmin/Rep/Shop controllers.
 *
 * IMPORTANT: order creation's validation rules genuinely differ by role
 * today — this isn't just "the same logic with renamed variables":
 *   - Shop enforces the 8am-4pm window, a zero-total check, and a
 *     duplicate-submission guard, and supports a Default (standing
 *     order) path.
 *   - Rep's time-window check is commented out (disabled); it has its
 *     own cart-empty and rep.access=='off' checks instead, no zero-total
 *     check, no duplicate-submission guard, and no Default path.
 *   - OrderAdmin has none of the above except the shared core below.
 * createOrder()/createDefaultOrder() below are Shop's exact composed
 * flow — Rep and OrderAdmin controllers compose the public assert*()
 * primitives directly, in their own order, matching their own real
 * rules, rather than being forced through Shop's.
 *
 * Throws \RuntimeException with the exact same user-facing message the
 * original controllers returned via back()->with('error', ...), so
 * callers can catch and display it the same way.
 */
class OrderService
{
    use PreventsDuplicateSubmission;

    private const ORDER_WINDOW_START = [8, 0];
    private const ORDER_WINDOW_END = [16, 15];

    public function assertPeriodAllowed(Shops $shop, string $period): void
    {
        if (!in_array($period, $shop->allowedOrderTimes(), true)) {
            throw new \RuntimeException('Invalid order time for this shop.');
        }
    }

    public function assertNoMismatchedItems(Shops $shop, string $period): void
    {
        $mismatched = $this->mismatchedCartItemNames($shop, $period);
        if ($mismatched->isNotEmpty()) {
            throw new \RuntimeException(
                'These items are not available for ' . $period . ' orders, please remove them from the cart: ' . $mismatched->implode(', ')
            );
        }
    }

    public function assertNoDuplicateForToday(Shops $shop, string $period): void
    {
        $alreadyOrderedToday = Orders::whereDate('created_at', Carbon::today())
            ->where('time_period', $period)
            ->where('shop', $shop->branch_code)
            ->exists();
        if ($alreadyOrderedToday) {
            throw new \RuntimeException('Order already created for this time period in this shop.');
        }
    }

    public function assertWithinOrderWindow(): void
    {
        $now = Carbon::now();
        [$startHour, $startMinute] = self::ORDER_WINDOW_START;
        [$endHour, $endMinute] = self::ORDER_WINDOW_END;
        if ($now->lt(Carbon::createFromTime($startHour, $startMinute)) || $now->gt(Carbon::createFromTime($endHour, $endMinute))) {
            throw new \RuntimeException('You can create orders between 8.00 a.m. to 4.00 p.m.');
        }
    }

    /**
     * Orders::create() + attaching the open cart to it. Does not log —
     * each caller logs its own exact (role-specific) message afterward.
     */
    public function persistPendingOrder(Shops $shop, string $period, float $total, ?string $note): Orders
    {
        $uniqueId = uniqid();

        $order = Orders::create([
            'unique_id' => $uniqueId,
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'total_price' => $total,
            'note' => $note,
            'time_period' => $period,
            'status' => 'Pending',
        ]);

        Cart::where('shop_bc_number', $shop->branch_code)
            ->whereNull('order_number')
            ->update(['order_number' => $uniqueId]);

        return $order;
    }

    /**
     * Shop's exact composed flow (duplicate-submission guard, zero-total
     * check, window check, plus the shared core). Only Shop\CartProcess
     * calls this — Rep/OrderAdmin compose the primitives above directly.
     */
    public function createOrder(Shops $shop, string $period, float $total, ?string $note, string $actorName): Orders
    {
        $this->guardAgainstDuplicateSubmission(
            'order-create',
            'Order Proceed button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        if ($total <= 0.0) {
            throw new \RuntimeException('Order total price value must not be 0.00/=');
        }

        $this->assertPeriodAllowed($shop, $period);
        $this->assertNoMismatchedItems($shop, $period);
        $this->assertWithinOrderWindow();
        $this->assertNoDuplicateForToday($shop, $period);

        $order = $this->persistPendingOrder($shop, $period, $total, $note);

        Logs::create([
            'type' => 'Create Order',
            'message' => "Created {$period} order. Order unique id : {$order->unique_id}",
            'user' => $actorName,
        ]);

        return $order;
    }

    public function createDefaultOrder(Shops $shop, string $period, float $total, ?string $name, string $actorName): Orders
    {
        $this->guardAgainstDuplicateSubmission(
            'order-create',
            'Order Proceed button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        $uniqueId = uniqid();

        $order = Orders::create([
            'unique_id' => $uniqueId,
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'total_price' => $total,
            'note' => '',
            'time_period' => 'Default',
            'status' => 'Default',
            'default_name' => $name,
            'default_period' => $period,
        ]);

        Cart::where('shop_bc_number', $shop->branch_code)
            ->whereNull('order_number')
            ->update(['order_number' => $uniqueId, 'default_name' => 'Default']);

        Logs::create([
            'type' => 'Create Order',
            'message' => "Created Default order. Order unique id : {$uniqueId}",
            'user' => $actorName,
        ]);

        return $order;
    }

    public function acceptPending(string $uniqueId, string $actorName): void
    {
        Orders::where('unique_id', $uniqueId)->update(['status' => 'Processing']);

        Logs::create([
            'type' => 'Accept Pending Order',
            'message' => "Accepted pending order {$uniqueId}",
            'user' => $actorName,
        ]);
    }

    public function cancelOrder(string $uniqueId, string $actorName): void
    {
        Orders::where('unique_id', $uniqueId)->update(['time_period' => 'Cancelled', 'status' => 'Cancelled']);

        Logs::create([
            'type' => 'Order update',
            'message' => "Cancelled order {$uniqueId}",
            'user' => $actorName,
        ]);
    }

    /**
     * The bulk Processing -> Complete transfer. Returns the number of
     * orders moved, system-wide (matches current behavior — not scoped by
     * date/route, which the audit flagged but wasn't asked to change).
     */
    public function transferProcessingToComplete(): int
    {
        return Orders::where('status', 'Processing')->update(['status' => 'Complete']);
    }

    private function mismatchedCartItemNames(Shops $shop, string $period)
    {
        return Cart::join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.shop_bc_number', $shop->branch_code)
            ->whereNull('carts.order_number')
            ->whereNotIn('products.order_time', [$period, 'All'])
            ->pluck('products.name_english');
    }
}
