<?php

namespace App\Services;

use App\Dtos\AddToCartDto;
use App\Dtos\UpdateCartItemsDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CartService
{
    private ?Cart $cachedCart = null;
    protected const COOKIE_CART_NAME = 'cart';
    protected const COOKIE_CART_ITEMS_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year

    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly ProductRepository $productRepository
    ){}

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

    /**
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    public function updateItemsQuantity(array $updateCartItemRequests):void{
        if(Auth::check()){
            $this->updateCartItemsInDatabase($updateCartItemRequests);
        }
        else{
            $this->updateCartItemsInCookies($updateCartItemRequests);
        }
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

    public function moveCartItemsToDatabase():void{
        $cookieCart = $this->getCartFromCookies();
        if($cookieCart->cartItems->isEmpty()){
            return;
        }

        $dbCart = $this->getCartFromDatabase();
        $dbCartItems = $dbCart->cartItems;

        $itemsToCreate = [];
        $itemsToUpdate = [];
        $idsToUpdate = [];

        foreach ($cookieCart->cartItems as $cookieItem) {
            $attributes = json_decode($cookieItem->attributes, true) ?? [];
            ksort($attributes);

            $existingItem = $dbCartItems->first(function (CartItem $dbItem) use ($cookieItem, $attributes) {
                return $dbItem->product_id === $cookieItem->product_id &&
                       json_decode($dbItem->attributes, true) === $attributes;
            });

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $cookieItem->quantity;
                $totalPrice = $newQuantity * $existingItem->unit_price;
                $itemsToUpdate[] = [
                    'id' => $existingItem->id,
                    'quantity' => $newQuantity,
                    'total_price' => $totalPrice,
                    'attributes' => json_encode($attributes),
                ];
                $idsToUpdate[] = $existingItem->id;
            } else {
                $itemsToCreate[] = new AddToCartDto(
                    $cookieItem->product_id,
                    $cookieItem->quantity,
                    $cookieItem->unit_price,
                    $attributes
                );
            }
        }

        if (!empty($itemsToCreate)) {
            $this->cartRepository->createCartItems($dbCart->id, $itemsToCreate);
        }

        if (!empty($itemsToUpdate)) {
            $this->cartRepository->batchUpdateCartItems($itemsToUpdate, $idsToUpdate);
        }

        $this->clearCartFromCookies();
        $this->recalculateCartTotalPrice();
        $this->cachedCart = $this->getCartFromDatabase();
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
    protected function fetchProductsToBeAdded(array $addToCartRequests):Collection{
        $productIds= array_map(fn($request):int => $request->getProductId(),$addToCartRequests);
        return $this->productRepository->findProductsByIds($productIds);
    }

    /**
     * @param AddToCartDto $request
     * @param Collection<int, Product> $productsToBeAdded
     */
    protected function setAttributesIfEmptyToRequest(AddToCartDto $request,Collection $productsToBeAdded): void
    {
        if (empty($request->getAttributes())) {
            $product = $productsToBeAdded->find($request->getProductId());
            if ($product && $product->attributes->isNotEmpty()) {
                $defaultAttributes = [];
                foreach ($product->attributes as $attribute) {
                    if ($attribute->attributeOptions->isNotEmpty()) {
                        $defaultAttributes[$attribute->id] = $attribute->attributeOptions->first()->id;
                    }
                }
                $request->setAttributes($defaultAttributes);
            }
        }
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

        $productsToBeAdded = $this->fetchProductsToBeAdded($addToCartRequests);

        foreach ($addToCartRequests as $request) {
            $this->setAttributesIfEmptyToRequest($request,$productsToBeAdded);
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
        $productsToBeAdded = $this->fetchProductsToBeAdded($addToCartRequests);
        foreach ($addToCartRequests as $request) {
            $this->setAttributesIfEmptyToRequest($request,$productsToBeAdded);
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

    /**
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    protected function updateCartItemsInDatabase(array $updateCartItemRequests): void
    {
        $cart = $this->getCartFromDatabase();
        $cartItems = $cart->cartItems;

        $updates = [];
        $idsToUpdate = [];

        foreach ($updateCartItemRequests as $request) {
            $cartItemId = $request->getCartItemId();
            $quantity = $request->getQuantity();
            $attributes = $request->getAttributes();
            ksort($attributes);

            $existingCartItem = $this->findExistingCartItemForUpdate($cartItems, $cartItemId);

            if ($existingCartItem !== null) {
                $totalPrice = $quantity * $existingCartItem->unit_price;
                $updates[] = [
                    'id' => $existingCartItem->id,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                    'attributes' => json_encode($attributes),
                ];
                $idsToUpdate[] = $existingCartItem->id;
            }
        }

        if (empty($updates)) {
            return;
        }

        $this->cartRepository->batchUpdateCartItems($updates, $idsToUpdate);

        $this->recalculateCartTotalPrice();
    }

    /**
     * @param Collection<int, CartItem> $cartItems
     * @param string $cartItemId
     * @return CartItem|null
     */
    protected function findExistingCartItemForUpdate(Collection $cartItems, string $cartItemId): CartItem|null
    {
        if ($cartItems->isEmpty()) {
            return null;
        }

        return $cartItems->first(function (CartItem $cartItem) use ($cartItemId) {
            return (isset($cartItem->id_from_cookie) && $cartItem->id_from_cookie === $cartItemId) ||
                   (isset($cartItem->id) && (string)$cartItem->id === $cartItemId);
        });
    }

    /**
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    protected function updateCartItemsInCookies(array $updateCartItemRequests):void{
        $cart = $this->getCartFromCookies();
        $cartItems = $cart->cartItems;

        $cartItemsForCookie = [];
        foreach ($cartItems as $item) {
            $attributeIds = json_decode($item->attributes, true) ?? [];
            ksort($attributeIds);
            $key = $item->product_id . '_' . json_encode($attributeIds);
            $cartItemsForCookie[$key] = [
                'id' => $item->id_from_cookie,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'attribute_ids' => $attributeIds,
            ];
        }

        foreach ($updateCartItemRequests as $request) {
            $cartItemId = $request->getCartItemId();
            $quantity = $request->getQuantity();
            $attributes = $request->getAttributes();
            ksort($attributes);
            $key = $request->getProductId() . '_' . json_encode($attributes);

            if (isset($cartItemsForCookie[$key]) && $cartItemsForCookie[$key]['id'] === $cartItemId) {
                $cartItemsForCookie[$key]['quantity'] = $quantity;
            }
        }

        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode($cartItemsForCookie), self::COOKIE_LIFETIME);

        $newCart = $this->getCartFromCookies();
        $cart->recalculateCartTotalPrice();
        $this->cachedCart = $newCart;

        $cartAttributes = collect($newCart->toArray())->only($newCart->getFillable())->toArray();
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);
    }

}
