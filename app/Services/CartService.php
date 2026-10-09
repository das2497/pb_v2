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
 * controllers. See PreventsDuplicateSubmission for why the debounce guard
 * is now scoped per actor instead of global.
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
    public function addToCart(Shops $shop, Products $product, int $qty, ?string $remark = null): Cart
    {
        $this->guardAgainstDuplicateSubmission(
            'cart-add',
            'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        if ($this->cartAlreadyHasDefaultItem($shop, $product->item_number)) {
            throw new \RuntimeException('Cart already have this item');
        }

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
    public function addAllToCart(Shops $shop, array $items, string $actorName): array
    {
        $this->guardAgainstDuplicateSubmission(
            'cart-add-all',
            'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.'
        );

        $created = [];

        foreach ($items as $entry) {
            $itemNumber = $entry['item_number'];
            $qty = (int) ($entry['qty'] ?? 0);

            if ($this->cartAlreadyHasDefaultItem($shop, $itemNumber)) {
                throw new \RuntimeException('Cart already have this item');
            }

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
        Cart::where('item_number', $itemNumber)
            ->where('shop_bc_number', $branchCode)
            ->whereNull('order_number')
            ->update(['qty' => $qty, 'remarke' => $remark]);

        Logs::create([
            'type' => 'Update quantity',
            'message' => "Updated [{$itemNumber}] item in the cart quantity : {$qty} remark : {$remark}",
            'user' => $actorName,
        ]);
    }

    public function deleteItem(string $itemNumber, string $branchCode, string $actorName): void
    {
        Cart::where('item_number', $itemNumber)
            ->where('shop_bc_number', $branchCode)
            ->whereNull('order_number')
            ->delete();

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
