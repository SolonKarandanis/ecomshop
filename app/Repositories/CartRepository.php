<?php

namespace App\Repositories;

use App\Dtos\AddToCartDto;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CartRepository
{
    public function modelQuery(): Builder| Cart{
        return Cart::query();
    }

    public function itemModelQuery(): Builder| CartItem{
        return CartItem::query();
    }

    public function getCart(int $userId): Cart{
        return $this->modelQuery()
            ->with(['cartItems'])
            ->where('user_id',$userId)
            ->firstOrCreate(['user_id'=>$userId,'total_price'=>0]);
    }

    public function getCartId(int $userId): int{
        $cart= $this->modelQuery()
            ->where('user_id',$userId)
            ->firstOrCreate(['user_id'=>$userId,'total_price'=>0]);
        return $cart->id;
    }

    public function saveCart(Cart $cart): void
    {
        $cart->update($cart->getFillable());
    }

    public function findItemByProductIdAndAttributes(int $cartId,int $productId, array $attributes): CartItem| null{
        return $this->itemModelQuery()
            ->where('cart_id',$cartId)
            ->where('product_id',$productId)
            ->where('attributes',json_encode($attributes))
            ->first();
    }

    public function updateItemQuantity(int $cartItemId, int $quantity): void{
        $this->itemModelQuery()->where('id',$cartItemId)->update([
            'quantity' => DB::raw('quantity+'.$quantity),
        ]);
    }

    public function createCartItem(int $cartId,AddToCartDto $addToCartDto):void{
        $total_price = $addToCartDto->getQuantity() * $addToCartDto->getPrice();
        $this->itemModelQuery()->create([
            'cart_id'=>$cartId,
            'product_id' => $addToCartDto->getProductId(),
            'quantity' => $addToCartDto->getQuantity(),
            'unit_price' => $addToCartDto->getPrice(),
            'total_price' => $total_price,
            'attributes' => json_encode($addToCartDto->getAttributes()),
        ]);
    }

    public function deleteCartItem(int $cartId,int $cartItemId):void{
        $this->itemModelQuery()
            ->where('id', $cartId)
            ->where('id', $cartItemId)
            ->delete();
    }

    public function deleteCartItems(int $cartId,array $cartItemIds):void{
        $this->itemModelQuery()
            ->where('id', $cartId)
            ->whereIn('id', $cartItemIds)
            ->delete();
    }

    public function clearCart(int $cartId):void{
        $this->itemModelQuery()
            ->where('cart_id',$cartId)
            ->delete();
    }

    /**
     * Creates multiple cart items from an array of AddToCartDto objects.
     * @param int $cartId
     * @param AddToCartDto[] $cartItems
     */
    public function createCartItems(int $cartId, array $cartItems): void
    {
        $itemsToInsert = collect($cartItems)->map(fn(AddToCartDto $dto) => [
            'cart_id' => $cartId,
            'product_id' => $dto->getProductId(),
            'quantity' => $dto->getQuantity(),
            'unit_price' => $dto->getPrice(),
            'total_price' => $dto->getQuantity() * $dto->getPrice(),
            'attributes' => json_encode($dto->getAttributes()),
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        if (!empty($itemsToInsert)) {
            $this->itemModelQuery()->insert($itemsToInsert);
        }
    }

    /**
     * Updates quantities for multiple cart items.
     * @param array $cartItems Array of items with 'id' and 'quantity' to add.
     */
    public function updateCartItemsQuantity(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $this->itemModelQuery()->where('id', $item['id'])->increment('quantity', $item['quantity']);
        }
    }

    public function updateCartItem(CartItem $cartItem): void{
        $this->itemModelQuery()->update($cartItem->toArray());
    }

    public function batchUpdateCartItems(array $updates, array $idsToUpdate): void
    {
        if (empty($updates)) {
            return;
        }

        $table = (new CartItem())->getTable();
        $cases = [];
        $params = [];

        $quantityCase = "quantity = CASE id ";
        $totalPriceCase = "total_price = CASE id ";
        $attributesCase = "attributes = CASE id ";

        foreach ($updates as $update) {
            $quantityCase .= "WHEN ? THEN ? ";
            $totalPriceCase .= "WHEN ? THEN ? ";
            $attributesCase .= "WHEN ? THEN ? ";
            $params[] = $update['id'];
            $params[] = $update['quantity'];
            $params[] = $update['id'];
            $params[] = $update['total_price'];
            $params[] = $update['id'];
            $params[] = $update['attributes'];
        }

        $quantityCase .= "END";
        $totalPriceCase .= "END";
        $attributesCase .= "END";

        $ids = implode(',', array_fill(0, count($idsToUpdate), '?'));

        $sql = "UPDATE {$table} SET {$quantityCase}, {$totalPriceCase}, {$attributesCase} WHERE id IN ({$ids})";

        $bindings = array_merge($params, $idsToUpdate);

        DB::update($sql, $bindings);
    }
}
