<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Services\Support\PreventsDuplicateSubmission;

/**
 * Centralizes add-to-cart / add-all-to-cart / update / delete, previously
 * reimplemented with small variations across OrderAdmin/Rep/Shop
 * controllers.
 *
 * IMPORTANT: the duplicate-submission guard and the audit-log message
 * both genuinely differ by role today, not just cosmetically — Shop has
 * a 2-second Cache guard on both add methods, Rep has a one-time
 * form-token guard on addAllToCart only (none on single add_to_cart),
 * OrderAdmin has no guard at all; and every role logs a different
 * message for the same mutation. addToCart()/addAllToCart()/updateQty()/
 * deleteItem() below are Shop's exact guarded+logged flow — Rep and
 * OrderAdmin controllers call the guard-free core methods directly and
 * do their own logging with their own exact message, rather than being
 * forced through Shop's.
 */
class CartService
{
    use PreventsDuplicateSubmission;

    /**
     * The duplicate-item guard only fires for an item already in the cart
     * as part of a Default (standing order) template — this is the real
     * current behavior, not a simplification: a second, unrelated add of
     * the same item_number is NOT blocked today.
     */
    public function assertNotDuplicateDefaultItem(Shops $shop, string $itemNumber): void
    {
        if ($this->cartAlreadyHasDefaultItem($shop, $itemNumber)) {
            throw new \RuntimeException('Cart already have this item');
        }
    }

    public function addItemToCart(Shops $shop, Products $product, float $qty, ?string $remark = null): Cart
    {
        $this->assertNotDuplicateDefaultItem($shop, $product->item_number);

        return Cart::create([
            'item_number' => $product->item_number,
            'qty' => $qty,
            'price' => $product->priceFor($shop),
            'remarke' => $remark,
            'shop_bc_number' => $shop->branch_code,
        ]);
    }

    /**
     * @param array<int, array{item_number: string, qty: float}> $items
     * @return Cart[]
     */
    public function addAllItemsToCart(Shops $shop, array $items): array
    {
        $created = [];

        foreach ($items as $entry) {
            $itemNumber = $entry['item_number'];
            $qty = round((float) ($entry['qty'] ?? 0), 2);

            $this->assertNotDuplicateDefaultItem($shop, $itemNumber);

            if ($qty <= 0) {
                continue;
            }

            $product = Products::where('item_number', $itemNumber)->first();
            if (!$product) {
                continue;
            }

            $created[] = Cart::create([
                'item_number' => $itemNumber,
                'qty' => $qty,
                'price' => $product->priceFor($shop),
                'shop_bc_number' => $shop->branch_code,
            ]);
        }

        return $created;
    }

    public function updateCartItemQty(string $itemNumber, string $branchCode, float $qty, ?string $remark): void
    {
        Cart::where('item_number', $itemNumber)
            ->where('shop_bc_number', $branchCode)
            ->whereNull('order_number')
            ->update(['qty' => $qty, 'remarke' => $remark]);
    }

    public function deleteCartItem(string $itemNumber, string $branchCode): void
    {
        Cart::where('item_number', $itemNumber)
            ->where('shop_bc_number', $branchCode)
            ->whereNull('order_number')
            ->delete();
    }

    /**
     * Adds a new item to an order that already exists (after creation,
     * from the "add item" screens), bumping the order's total_price by
     * the item's contribution. Distinct from addItemToCart() above,
     * which targets the pre-order cart and enforces the default-item
     * duplicate guard — none of the 8 "add item to an existing order"
     * controllers (OrderAdmin/Rep/Shop x Pending/Processing/Under-Review/
     * Default) check for that, so this primitive doesn't either.
     */
    public function addItemToExistingOrder(string $orderNumber, string $itemNumber, string $branchCode, float $price, float $qty): Cart
    {
        $cart = Cart::create([
            'item_number' => $itemNumber,
            'qty' => $qty,
            'price' => $price,
            'shop_bc_number' => $branchCode,
            'order_number' => $orderNumber,
        ]);

        Orders::where('unique_id', $orderNumber)->increment('total_price', $price * $qty);

        return $cart;
    }

    /**
     * Adjusts an already-placed order's total_price by the price delta
     * and updates the matching cart row's qty/remark. Shared, byte-for-
     * byte identical core of update() on every OrderAdmin/Rep/Shop
     * Pending/Processing/Under-Review listing controller — only the
     * access gate and logging differ by role/controller, so callers
     * keep doing those themselves.
     */
    public function applyOrderItemQtyChange(string $orderNumber, string $itemNumber, float $price, float $qty, ?string $remark): void
    {
        $qtyCart = Cart::where('order_number', $orderNumber)->where('item_number', $itemNumber)->first()->qty;

        Orders::where('unique_id', $orderNumber)->increment('total_price', $price * ($qty - $qtyCart));

        Cart::where('order_number', $orderNumber)
            ->where('item_number', $itemNumber)
            ->update(['qty' => $qty, 'remarke' => $remark]);
    }

    /**
     * The shared core of delete() on the same set of controllers:
     * reverses the item's contribution to total_price, then removes
     * the cart row.
     */
    public function removeOrderItem(string $orderNumber, string $itemNumber, float $price, float $qty): void
    {
        Orders::where('unique_id', $orderNumber)->decrement('total_price', $price * $qty);

        Cart::where('order_number', $orderNumber)->where('item_number', $itemNumber)->delete();
    }

    // ---- Shop's exact guarded + logged flow ----

    public function addToCart(Shops $shop, Products $product, float $qty, ?string $remark = null): Cart
    {
        $this->guardAgainstDuplicateSubmission(
            'cart-add',
            'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        return $this->addItemToCart($shop, $product, $qty, $remark);
    }

    public function addAllToCart(Shops $shop, array $items, string $actorName): array
    {
        $this->guardAgainstDuplicateSubmission(
            'cart-add-all',
            'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        $created = $this->addAllItemsToCart($shop, $items);

        if (!empty($created)) {
            Logs::create([
                'type' => 'Add To Cart',
                'message' => 'Added to cart items : ' . implode(' ', array_map(fn ($c) => $c->item_number, $created)),
                'user' => $actorName,
            ]);
        }

        return $created;
    }

    public function updateQty(string $itemNumber, string $branchCode, float $qty, ?string $remark, string $actorName): void
    {
        $this->updateCartItemQty($itemNumber, $branchCode, $qty, $remark);

        Logs::create([
            'type' => 'Update quantity',
            'message' => "Updated [{$itemNumber}] item in the cart quantity : {$qty} remark : {$remark}",
            'user' => $actorName,
        ]);
    }

    public function deleteItem(string $itemNumber, string $branchCode, string $actorName): void
    {
        $this->deleteCartItem($itemNumber, $branchCode);

        Logs::create([
            'type' => 'Delete item',
            'message' => "Deleted [{$itemNumber}] item from cart",
            'user' => $actorName,
        ]);
    }

    private function cartAlreadyHasDefaultItem(Shops $shop, string $itemNumber): bool
    {
        return Cart::where('item_number', $itemNumber)
            ->where('shop_bc_number', $shop->branch_code)
            ->whereNotNull('default_name')
            ->whereNull('order_number')
            ->exists();
    }
}
