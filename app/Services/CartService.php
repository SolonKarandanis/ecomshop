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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CartService
{
    protected const COOKIE_CART_NAME = 'cart';
    protected const COOKIE_CART_ITEMS_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year
    private ?Cart $cachedCart = null;

    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly ProductRepository $productRepository
    ){}

    public function getCart(): Cart{
        Log::debug('Cached cart', [!is_null($this->cachedCart)]);
        if($this->cachedCart){
            return $this->cachedCart;
        }
        if(Auth::check()){
            Log::debug('Getting cart from database');
            $cart = $this->getCartFromDatabase();
        }
        else{
            Log::debug('Getting cart from cookies');
            $cart = $this->getCartFromCookies();
        }
        $this->cachedCart = $cart;
        return $this->cachedCart;
    }

    protected function getCartFromCookies(): Cart
    {
        $cartData = json_decode($this->getFromCookies(self::COOKIE_CART_NAME),true);
        $cart = new Cart($cartData);

        $cookieValue =$this->getFromCookies(self::COOKIE_CART_ITEMS_NAME);
        Log::debug('Raw cartItems cookie value from request(): ', [$cookieValue]);
        $cartItemsData = json_decode($cookieValue, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::debug('JSON decode error: ' . json_last_error_msg());
            $cartItemsData = [];
        }

        $cartItems = [];
        $productIds = [];
        foreach ($cartItemsData as $key => $itemData) {
            $productId = $itemData['product_id'];
            $productIds[] = $productId;

            $modelData = [
                'product_id' => $productId,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
                'total_price' => $itemData['price'] * $itemData['quantity'],
                'attributes' => $itemData['attribute_ids'],
            ];
            $cartItem = new CartItem($modelData);
            // This is a bit of a hack to keep the cookie item id without modifying the model
            $cartItem->id_from_cookie = $itemData['id'] ?? null;
            $cartItems[] = $cartItem;
        }
        $products = $this->productRepository->findProductsForCart($productIds);
        foreach ($cartItems as $cartItem) {
            $product = $products->get($cartItem->product_id);
            if($product){
                $cartItem->setRelation('product', $product);
            }
        }
        $cart->setRelation('cartItems', collect($cartItems));
        $this->recalculateCartTotalPrice($cart);

        return $cart;
    }

    protected function getCartFromDatabase():Cart{
        $userId=Auth::id();
        return $this->cartRepository->getCart($userId);
    }

    /**
     * @param AddToCartDto[] $addToCartRequests
     */
    public function addItemsToCart(array $addToCartRequests):void{
        if(Auth::check()){
            $this->saveCartToDatabase($addToCartRequests);
        }
        else{
            $this->saveCartToCookies($addToCartRequests);
        }
    }

    /**
     * @param AddToCartDto[] $addToCartRequests
     */
    public function saveCartToDatabase(array $addToCartRequests): void
    {
        $cartId = $this->cartRepository->getCartId(Auth::id());
        Log::debug('Cart id ', [$cartId]);
        $productsToBeAdded = $this->fetchProductsToBeAdded($addToCartRequests);
        $newCartItems = [];
        foreach ($addToCartRequests as $request) {
            $this->setAttributesIfEmptyToRequest($request, $productsToBeAdded);
            $attributes = $request->getAttributes();
            ksort($attributes);
            $request->setAttributes($attributes);
            $existingItem = $this->cartRepository->findItemByProductIdAndAttributes(
                $cartId,
                $request->getProductId(),
                $attributes
            );

            // Check if the item is already in $newCartItems to be added
            $alreadyInNewItems = false;
            foreach ($newCartItems as $newItemDto) {
                if ($newItemDto->getProductId() === $request->getProductId()) {
                    $newItemAttributes = $newItemDto->getAttributes();
                    ksort($newItemAttributes);
                    if ($newItemAttributes === $attributes) {
                        $newItemDto->setQuantity($newItemDto->getQuantity() + $request->getQuantity());
                        $alreadyInNewItems = true;
                        break;
                    }
                }
            }

            if ($alreadyInNewItems) {
                continue;
            }

            $product = $productsToBeAdded->find($request->getProductId());
            $price = $this->calculatePriceWithAttributes($product, $attributes);
            $request->setPrice($price);

            if ($existingItem) {
                $request->setQuantity($existingItem->quantity + $request->getQuantity());
                $totalPrice = $request->getQuantity()* $request->getPrice();
                $this->cartRepository->updateItemQuantity($existingItem->id, $request->getQuantity(),$request->getPrice(), $totalPrice);
            } else {
                $newCartItems[] = $request;
            }
        }
        if (!empty($newCartItems)) {
            $this->cartRepository->createCartItems($cartId, $newCartItems);
        }
        $this->recalculateCartTotalPrice();
    }

    /**
     * @param AddToCartDto[] $addToCartRequests
     */
    public function saveCartToCookies(array $addToCartRequests): void
    {
        $cart = $this->getCart();
        $productsToBeAdded = $this->fetchProductsToBeAdded($addToCartRequests);
        foreach ($addToCartRequests as $request) {
            $this->setAttributesIfEmptyToRequest($request, $productsToBeAdded);
            $attributes = $request->getAttributes();
            ksort($attributes);
            $existingItem = $cart->cartItems->first(function (CartItem $item) use ($request, $attributes) {
                $itemAttributes = $item->attributes ?? [];
                if (is_string($itemAttributes)) {
                    $itemAttributes = json_decode($itemAttributes, true) ?? [];
                }
                ksort($itemAttributes);
                return (int)$item->product_id === (int)$request->getProductId() && $itemAttributes === $attributes;
            });
            $product = $productsToBeAdded->find($request->getProductId());
            $price = $this->calculatePriceWithAttributes($product, $attributes);
            if ($existingItem) {
                $existingItem->quantity += $request->getQuantity();
            } else {
                $newItemData = [
                    'product_id' => $request->getProductId(),
                    'quantity' => $request->getQuantity(),
                    'unit_price' => $price,
                    'attributes' => $attributes,
                ];
                $newItem = new CartItem($newItemData);
                $newItem->id_from_cookie = (string) Str::uuid();
                $cart->cartItems->push($newItem);
            }
        }
        $cartItemsForCookie = [];
        foreach ($cart->cartItems as $item) {
            $itemAttributes = $item->attributes ?? [];
            ksort($itemAttributes);
            $cartItemsForCookie[] = [
                'id' => $item->id_from_cookie,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'attribute_ids' => $itemAttributes,
            ];
        }
        $this->putItemsToCookies($cartItemsForCookie);
        $this->recalculateCartTotalPrice();
    }

    /**
     * @param Cart $cart
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    public function updateItemsQuantity(Cart $cart,array $updateCartItemRequests):void{
        if(Auth::check()){
            $this->updateCartItemsInDatabase($cart,$updateCartItemRequests);
        }
        else{
            $this->updateCartItemsInCookies($cart,$updateCartItemRequests);
        }
    }

    /**
     * @param Cart $cart
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    protected function updateCartItemsInDatabase(Cart $cart,array $updateCartItemRequests): void
    {
        Log::debug('Attempting to update cart items in the database.');
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
                    'attributes' => $attributes,
                ];
                $idsToUpdate[] = $existingCartItem->id;

                $existingCartItem->quantity = $quantity;
                $existingCartItem->total_price = $totalPrice;
                $existingCartItem->attributes = $attributes;
            }
        }
        if (empty($updates)) {
            return;
        }
        $this->cartRepository->batchUpdateCartItems($updates, $idsToUpdate);
        $this->recalculateCartTotalPrice($cart);
    }

    /**
     * @param Cart $cart
     * @param UpdateCartItemsDTO[] $updateCartItemRequests
     */
    protected function updateCartItemsInCookies(Cart $cart,array $updateCartItemRequests):void{
        $cartItems = $cart->cartItems;
        $cartItemsForCookie = [];
        foreach ($cartItems as $item) {
            $attributeIds = $item->attributes ?? [];
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
                Log::debug('Updating quantity in cookie for item: ' . $cartItemId . ' to quantity: ' . $quantity);
                $cartItemsForCookie[$key]['quantity'] = $quantity;
            }
        }
        $this->putItemsToCookies($cartItemsForCookie);
        $updatedCartItems = [];
        foreach ($cartItemsForCookie as $itemData) {
            $modelData = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
                'total_price' => $itemData['price'] * $itemData['quantity'],
                'attributes' => $itemData['attribute_ids'],
            ];
            $cartItem = new CartItem($modelData);
            $cartItem->id_from_cookie = $itemData['id'] ?? null;
            $updatedCartItems[] = $cartItem;
        }
        // Eager load the product relationships for the updated cart items
        $productIds = array_column($updatedCartItems, 'product_id');
        if (!empty($productIds)) {
            $products = $this->productRepository->findProductsByIds($productIds)->keyBy('id');
            foreach ($updatedCartItems as $cartItem) {
                if (isset($products[$cartItem->product_id])) {
                    $cartItem->setRelation('product', $products[$cartItem->product_id]);
                }
            }
        }
        $cart->setRelation('cartItems', collect($updatedCartItems));
        $this->recalculateCartTotalPrice($cart);
    }

    public function removeItemsFromCart(array $cartItemIds):void{
        if(Auth::check()){
            $this->deleteItemsFromDatabase($cartItemIds);
        }else{
            $this->deleteItemsFromCookies($cartItemIds);
        }
    }

    protected function deleteItemsFromDatabase(array $cartItemIds):void{
        $cartId=$this->cartRepository->getCartId(Auth::id());
        $this->cartRepository->deleteCartItems($cartId,$cartItemIds);
        $this->recalculateCartTotalPrice();
    }

    protected function deleteItemsFromCookies(array $cartItemIds):void{
        $cart = $this->getCart();

        $itemsToKeep = $cart->cartItems->reject(function ($item) use ($cartItemIds) {
            return in_array($item->id_from_cookie, $cartItemIds);
        });

        $cart->setRelation('cartItems', $itemsToKeep);

        $cartItemsForCookie = [];
        foreach ($itemsToKeep as $item) {
            $cartItemsForCookie = array_merge($cartItemsForCookie, $this->getCartItemsForCookies($item));
        }
        Log::debug('Remaining cart items after delete: ', [$cartItemsForCookie]);
        $this->putItemsToCookies($cartItemsForCookie);
        $this->recalculateCartTotalPrice($cart);
    }

    public function clearCart():void{
        if(Auth::check()){
            $this->clearCartFromDatabase();
        }else{
            $this->clearCartFromCookies();
        }
        $this->cachedCart = null;
    }

    protected function clearCartFromDatabase():void{
        $cartId=$this->cartRepository->getCartId(Auth::id());
        $this->cartRepository->clearCart($cartId);
        $this->recalculateCartTotalPrice();
    }

    protected function clearCartFromCookies():void{
        Cookie::queue(Cookie::forget(self::COOKIE_CART_NAME));
        Cookie::queue(Cookie::forget(self::COOKIE_CART_ITEMS_NAME));
    }

    public function getCartItemsCount(): int
    {
        if(Auth::check()){
            return $this->cartRepository->getCartItemsCount(Auth::id());
        }else{
            return $this->getCart()->cartItems->count();
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
            $attributes = $cookieItem->attributes ?? [];
            ksort($attributes);
            $existingItem = $dbCartItems->first(function (CartItem $dbItem) use ($cookieItem, $attributes) {
                $dbAttributes = $dbItem->attributes ?? [];
                if (is_string($dbAttributes)) {
                    $dbAttributes = json_decode($dbAttributes, true) ?? [];
                }
                ksort($dbAttributes);
                return (int)$dbItem->product_id === (int)$cookieItem->product_id &&
                       $dbAttributes === $attributes;
            });
            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $cookieItem->quantity;
                $totalPrice = $newQuantity * $existingItem->unit_price;
                $itemsToUpdate[] = [
                    'id' => $existingItem->id,
                    'quantity' => $newQuantity,
                    'total_price' => $totalPrice,
                    'attributes' => $attributes,
                ];
                $idsToUpdate[] = $existingItem->id;
            } else {
                $itemsToCreate[] = AddToCartDto::withAttributes(
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
        $dbCart = $this->getCartFromDatabase();
        $this->clearCartFromCookies();
        $this->recalculateCartTotalPrice($dbCart);
    }

    protected function getFromCookies(string $cookieName): array|string|null{
            return request()->cookie($cookieName,'[]');
    }

    protected function getCartItemsForCookies($item):array{
        $attributeIds = $item->attributes ?? [];
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

    private function calculatePriceWithAttributes(Product $product, array $attributes): float
    {
        $newPrice = $product->price;
        $attributeValues = $product->productAttributeValues;

        foreach ($attributes as $attributeId => $optionId) {
            $value = $attributeValues->first(function ($item) use ($attributeId, $optionId) {
                return $item->attribute_id == $attributeId && $item->attribute_option_id == $optionId;
            });

            if ($value) {
                if ($value->attribute_value_method === 'attribute.value.method.fixed') {
                    $newPrice += (float)$value->attribute_value;
                } elseif ($value->attribute_value_method === 'attribute.value.method.percent') {
                    $newPrice *= (1 + (float)$value->attribute_value / 100);
                }
            }
        }

        return $newPrice;
    }

    protected function recalculateCartTotalPrice(?Cart $cart = null):void{
        if($cart === null){
            $cart = $this->getCart();
        }
        $cart->recalculateCartTotalPrice();
        if(Auth::check()){
            $this->cartRepository->saveCart($cart);
        }else{
            $cartAttributes = collect($cart->toArray())->only($cart->getFillable())->toArray();
            $this->putCartInCookies($cartAttributes);
        }
        $this->cachedCart=$cart;
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

    protected function putCartInCookies(array $cartAttributes):void{
        Cookie::queue(self::COOKIE_CART_NAME, json_encode($cartAttributes), self::COOKIE_LIFETIME);
    }

    protected function putItemsToCookies(array $cartItemsForCookie):void{
        Cookie::queue(self::COOKIE_CART_ITEMS_NAME, json_encode(array_values($cartItemsForCookie)), self::COOKIE_LIFETIME);
    }

}
