<?php

namespace App\Services;

use App\Dtos\AddToCartDto;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    private ?Cart $cachedCart = null;
    protected const COOKIE_CART_NAME = 'cart';
    protected const COOKIE_CART_ITEMS_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year

    public function __construct(private readonly CartRepository $cartRepository){}

    public function getCart(): Cart{
        if ($this->cachedCart !== null) {
            return $this->cachedCart;
        }

        if(Auth::check()){
            $cart = $this->getCartFromDatabase();
        }
        else{
            $cart = $this->getCartFromCookies();
        }
        $this->cachedCart = $cart;
        return $cart;
    }

    public function addItemToCart(AddToCartDto $addToCartRequest):void{
        if(Auth::check()){
            $this->saveCartToDatabase($addToCartRequest);
        }
        else{
            $this->saveCartToCookies($addToCartRequest);
        }
    }

    protected function getCartFromCookies(): Cart
    {
        $cartData = json_decode(Cookie::get(self::COOKIE_CART_NAME), true) ?? [];
        $cart = new Cart($cartData);

        $cartItemsData = json_decode(Cookie::get(self::COOKIE_CART_ITEMS_NAME, '[]'), true);
        $cartItems = [];
        foreach ($cartItemsData as $key => $itemData) {
            $modelData = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
                'total_price' => $itemData['price'] * $itemData['quantity'],
                'attributes' => json_encode($itemData['attribute_ids']),
            ];
            $cartItem = new CartItem($modelData);
            // This is a bit of a hack to keep the cookie item id without modifying the model
            $cartItem->id_from_cookie = $itemData['id'] ?? null;
            $cartItems[] = $cartItem;
        }

        $cart->setRelation('cartItems', collect($cartItems));
        $cart->recalculateCartTotalPrice();

        return $cart;
    }



    protected function getCartFromDatabase():Cart{
        $userId=Auth::id();
        return $this->cartRepository->getCart($userId);
    }

    public function saveCartToCookies(AddToCartDto $addToCartRequest): void
    {
        $cart = $this->getCartFromCookies();
        $cartItemsForCookie = [];
        foreach ($cart->cartItems as $item) {
            $attributeIds = json_decode($item->attributes, true) ?? [];
            ksort($attributeIds);
            $key = $item->product_id . '_' . json_encode($attributeIds);

            $itemData = [
                'id' => $item->id_from_cookie ?? (string) Str::uuid(),
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'attribute_ids' => $attributeIds,
            ];
            $cartItemsForCookie[$key] = $itemData;
        }
        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItemsForCookie), self::COOKIE_LIFETIME);

        $cart->recalculateCartTotalPrice();
        $cartAttributes = collect($cart->toArray())->only($cart->getFillable())->toArray();
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);

        $this->cachedCart = $cart; // Update cache after modification
    }

    public function saveCartToDatabase(AddToCartDto $addToCartRequest): void{
        $cart = $this->getCartFromDatabase();
        $attributes = $addToCartRequest->getAttributes();
        ksort($attributes);
        $addToCartRequest->setAttributes($attributes);
        $cartItem = $this->cartRepository->findItemByProductIdAndAttributes($cart->id,$addToCartRequest->getProductId(), $attributes);
        if($cartItem){
            $this->cartRepository->updateItemQuantity($cartItem->id, $addToCartRequest->getQuantity());
        }
        else{
            $this->cartRepository->createCartItem($addToCartRequest);
        }

        $cart = $this->getCartFromDatabase();
        $cart->recalculateCartTotalPrice();
        $this->cartRepository->saveCart($cart);
        $this->cachedCart = $cart;
    }
}
