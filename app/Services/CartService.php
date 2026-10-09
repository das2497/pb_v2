<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Logs;
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

    public function addItemToCart(Shops $shop, Products $product, int $qty, ?string $remark = null): Cart
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
     * @param array<int, array{item_number: string, qty: int}> $items
     * @return Cart[]
     */
    public function addAllItemsToCart(Shops $shop, array $items): array
    {
        $created = [];

        foreach ($items as $entry) {
            $itemNumber = $entry['item_number'];
            $qty = (int) ($entry['qty'] ?? 0);

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

    public function updateCartItemQty(string $itemNumber, string $branchCode, int $qty, ?string $remark): void
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

    // ---- Shop's exact guarded + logged flow ----

    public function addToCart(Shops $shop, Products $product, int $qty, ?string $remark = null): Cart
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

    public function updateQty(string $itemNumber, string $branchCode, int $qty, ?string $remark, string $actorName): void
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
