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

    public function addItemsToCart(array $addToCartRequests):void{
        if(Auth::check()){
            $this->saveCartToDatabase($addToCartRequests);
        }
        else{
            $this->saveCartToCookies($addToCartRequests);
        }
    }

    public function updateItemsQuantity():void{

    }

    public function removeItemsFromCart(array $cartItemIds):void{
        if(Auth::check()){
            $this->deleteItemsFromDatabase($cartItemIds);
        }else{
            $this->deleteItemsFromCookies($cartItemIds);
        }
    }

    public function clearCart():void{
        if(Auth::check()){
            $this->clearCartFromDatabase();
        }else{
            $this->clearCartFromCookies();
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

    protected function getCartItemsForCookies($item):array{
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
        return $cartItemsForCookie;
    }

    /**
     * @param AddToCartDto[] $addToCartRequests
     */
    public function saveCartToCookies(array $addToCartRequests): void
    {
        $cart = $this->getCartFromCookies();
        $cartItemsForCookie = [];
        foreach ($cart->cartItems as $item) {
            $cartItemsForCookie= $this->getCartItemsForCookies($item);
        }

        foreach ($addToCartRequests as $request) {
            $attributes = $request->getAttributes();
            ksort($attributes);
            $key = $request->getProductId() . '_' . json_encode($attributes);

            if (isset($cartItemsForCookie[$key])) {
                $cartItemsForCookie[$key]['quantity'] += $request->getQuantity();
            } else {
                $cartItemsForCookie[$key] = [
                    'id' => (string) Str::uuid(),
                    'product_id' => $request->getProductId(),
                    'quantity' => $request->getQuantity(),
                    'price' => $request->getPrice(),
                    'attribute_ids' => $attributes,
                ];
            }
        }

        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItemsForCookie), self::COOKIE_LIFETIME);

        $cart->recalculateCartTotalPrice();
        $cartAttributes = collect($cart->toArray())->only($cart->getFillable())->toArray();
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);

        $this->cachedCart = $cart; // Update cache after modification
    }

    /**
     * @param AddToCartDto[] $addToCartRequests
     */
    public function saveCartToDatabase(array $addToCartRequests): void{
        $cartId = $this->cartRepository->getCartId(Auth::id());

        foreach ($addToCartRequests as $request) {
            $attributes = $request->getAttributes();
            ksort($attributes);
            $request->setAttributes($attributes);
        }

        $this->cartRepository->createCartItems($cartId, $addToCartRequests);
        $this->recalculateCartTotalPrice();
    }

    protected function deleteItemsFromDatabase(array $cartItemIds):void{
        $cartId=$this->cartRepository->getCartId(Auth::id());
        $this->cartRepository->deleteCartItems($cartId,$cartItemIds);
        $this->recalculateCartTotalPrice();
    }

    protected function deleteItemsFromCookies(array $cartItemIds):void{
        $cart = $this->getCartFromCookies();
        $cartItemsForCookie = [];
        foreach ($cart->cartItems as $item) {
            if(in_array($item->id_from_cookie, $cartItemIds)){
                continue;
            }
            $cartItemsForCookie=$this->getCartItemsForCookies($item);
        }

        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItemsForCookie), self::COOKIE_LIFETIME);

        $cart->recalculateCartTotalPrice();
        $cartAttributes = collect($cart->toArray())->only($cart->getFillable())->toArray();
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);

        $this->cachedCart = $cart;
    }

    protected function clearCartFromDatabase():void{
        $cartId=$this->cartRepository->getCartId(Auth::id());
        $this->cartRepository->clearCart($cartId);
        $this->recalculateCartTotalPrice();
    }

    protected function clearCartFromCookies():void{
        Cookie::queue(Cookie::forget(self::COOKIE_CART_NAME));
        Cookie::queue(Cookie::forget(self::COOKIE_CART_ITEMS_NAME));
        $this->cachedCart = new Cart();
    }

    protected function recalculateCartTotalPrice():void{
        $cart = $this->getCartFromDatabase();
        $cart->recalculateCartTotalPrice();
        $this->cartRepository->saveCart($cart);
        $this->cachedCart = $cart;
    }
}
