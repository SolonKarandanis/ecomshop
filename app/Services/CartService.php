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
        return json_decode(Cookie::get(self::COOKIE_CART_ITEMS_NAME,'[]'),true);
    }

    protected function getCartFromCookies(): Cart
    {
        $cartData = json_decode(Cookie::get(self::COOKIE_CART_NAME), true) ?? [];
        $cart = new Cart($cartData);

        $cartItemsData = $this->getCartItemsFromCookies();
        $cartItems = [];
        foreach ($cartItemsData as $itemData) {
            $cartItems[] = new CartItem($itemData);
        }

        $cart->setRelation('cartItems', collect($cartItems));

        return $cart;
    }

    protected function getCartFromDatabase():Cart{
        $userId=Auth::id();
        return $this->cartRepository->getCart($userId);
    }

}
