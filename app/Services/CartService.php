<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_CART_NAME = 'cart';
    protected const COOKIE_CART_ITEMS_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year

    public function __construct(private readonly CartRepository $cartRepository){}

    public function getCart(): Cart{
        if(Auth::check()){
            return $this->getCartFromDatabase();
        }
        else{
            return $this->getCartFromCookies();
        }
    }

    protected function saveItemToCookies(int $productId,int $quantity,int $price,array $attributeIds):void{
        $cartItems = $this->getCartItemsFromCookies();
        ksort($attributeIds);
        $itemKey = $productId.'_'.json_encode($attributeIds);
        if(isset($cartItems[$itemKey])){
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price;
        }
        else{
            $cartItems[$itemKey] = [
                'id'=> Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'attribute_ids' => $attributeIds,
            ];

            Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
        }
    }

    protected function getCartItemsFromCookies():array{
        if ($this->cachedCartItems === null) {
            $this->cachedCartItems = json_decode(Cookie::get(self::COOKIE_CART_ITEMS_NAME, '[]'), true);
        }
        return $this->cachedCartItems;
    }

    protected function getCartFromCookies(): Cart
    {
        $cartData = json_decode(Cookie::get(self::COOKIE_CART_NAME), true) ?? [];
        $cart = new Cart($cartData);

        $cartItemsData = $this->getCartItemsFromCookies();
        $cartItems = [];
        foreach ($cartItemsData as $itemData) {
            $modelData = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
                'total_price' => $itemData['price'] * $itemData['quantity'],
                'attributes' => json_encode($itemData['attribute_ids']),
            ];
            $cartItems[] = new CartItem($modelData);
        }

        $cart->setRelation('cartItems', collect($cartItems));
        $cart->recalculateCartTotalPrice();

        return $cart;
    }

    protected function deleteItemFromCookies(int $productId,array $attributes):void{
        $cartItems= $this->getCartItemsFromCookies();
        ksort($attributes);
        $cartKey = $productId.'_'.json_encode($attributes);

        //Remove item from cart
        unset($cartItems[$cartKey]);

        $cart = $this->getCartFromCookies();
        $cart->recalculateCartTotalPrice();
        $this->saveCartToCookies($cart);
    }

    protected function getCartFromDatabase():Cart{
        $userId=Auth::id();
        return $this->cartRepository->getCart($userId);
    }

    public function saveCartToCookies(Cart $cart): void
    {
        $cart->recalculateCartTotalPrice();

        $cartAttributes = collect($cart->toArray())->only($cart->getFillable())->toArray();
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);

        $cartItemsForCookie = [];
        foreach ($cart->cartItems as $item) {
            $attributeIds = json_decode($item->attributes, true) ?? [];
            ksort($attributeIds);
            $key = $item->product_id . '_' . json_encode($attributeIds);

            // Try to find existing UUID from cache, which is populated when cart is read
            $uuid = $this->cachedCartItems[$key]['id'] ?? null;

            $itemData = [
                'id' => $uuid ?? (string) Str::uuid(),
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'attribute_ids' => $attributeIds,
            ];
            $cartItemsForCookie[$key] = $itemData;
        }

        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItemsForCookie), self::COOKIE_LIFETIME);
        $this->cachedCartItems = $cartItemsForCookie; // Update cache after modification
    }
}
