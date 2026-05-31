<?php

namespace App\Services;

use App\Dtos\CheckoutDTO;
use App\Dtos\CreateOrderDTO;
use App\Dtos\OrderSearchRequestDTO;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\StripePaymentStatusEnum;
use App\Models\CartItem;
use App\Payments\PaymentHandlerFactory;
use App\Exceptions\EmptyCartException;
use App\Exceptions\OrderException;
use App\Exceptions\PaymentException;
use App\Exports\OrdersExport;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly CartService $cartService,
        private readonly StripeService $stripeService,
        private readonly PaymentHandlerFactory $paymentHandlerFactory,
    ){}

    public function getOrderById(int $orderId):Order{
        return $this->orderRepository->getOrderById($orderId);
    }

    public function getUsersLatestOrder(int $userId):Order{
        return $this->orderRepository->getLatestOrder($userId);
    }

    public function getUsersOrders(OrderSearchRequestDTO $dto): LengthAwarePaginator|array{
        return $this->orderRepository->getUsersOrders($dto);
    }

    public function countOrders(OrderSearchRequestDTO $dto): int
    {
        return $this->orderRepository->countOrders($dto);
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportOrders(OrderSearchRequestDTO $dto): BinaryFileResponse
    {
        return Excel::download(new OrdersExport($this->orderRepository, $dto), 'orders.xlsx');
    }

    /**
     * @throws OrderException
     * @throws EmptyCartException|PaymentException
     */
    public function checkout(CheckoutDTO $dto):string{
        $paymentMethod=$dto->getPaymentMethod();
        DB::beginTransaction();
            try{
                $cart = $this->cartService->getCart();
                Log::debug('OrderService checkout cartItems count: ', [$cart->cartItems->count()]);
                if ($cart->cartItems->isEmpty()) {
                    throw new EmptyCartException('Cart is empty');
                }
                $line_items = $this->createLineItems($cart->cartItems);
                $order_items = $this->createOrderItems($cart->cartItems);
                $paymentMethods = $this->paymentMethodRepository->findAll()->pluck('id', 'resource_key');
                $paymentMethodId=$paymentMethods->get($paymentMethod);
                Log::debug('OrderService creating order');
                $order = $this->createNewOrder($cart->total_price,$paymentMethodId,$order_items);
                Log::debug('OrderService created order ',[$order->id]);
                $redirect_url = $this->paymentHandlerFactory->make($paymentMethod)->process($order, $line_items);
                Log::debug('OrderService creating address');
                $this->addressRepository->create($order->id,$dto);
                Log::debug('OrderService created address');
                Log::debug('OrderService clearing cart');
                $this->cartService->clearCart();
            DB::commit();
            $order = $this->getUsersLatestOrder(auth()->user()->id);
            try {
                Mail::to(request()->user())->send(new OrderPlaced($order));
            } catch (\Exception $e) {
                Log::error('OrderService mail sending failed: ' . $e->getMessage());
            }
            return $redirect_url;
        }
        catch (EmptyCartException|PaymentException $exception){
            DB::rollBack();
            throw $exception;
        }
        catch (Throwable $exception){
            Log::error($exception);
            DB::rollBack();
            throw new OrderException('Something went wrong during checkout!', 0, $exception);
        }
    }

    /**
     * @param Collection<int, CartItem> $cartItems
     * @return array
     */
    protected function createLineItems(Collection $cartItems):array{
        return $cartItems->map(fn(CartItem $cartItem) => [
            'price_data' => [
                'currency'     => config('app.currency'),
                'unit_amount'  => $cartItem->unit_price * 100, // stripe wants cents
                'product_data' => ['name' => $cartItem->product->name],
            ],
            'quantity' => $cartItem->quantity,
        ])->values()->all();
    }

    /**
     * @param Collection<int, CartItem> $cartItems
     * @return array
     */
    protected function createOrderItems(Collection $cartItems):array{
        return $cartItems->map(fn(CartItem $cartItem) => (new OrderItem($cartItem))->attributesToArray())->values()->all();
    }

    /**
     * @throws OrderException
     */
    protected function createNewOrder(int $totalPrice, int $paymentMethodId, array $orderItems): Order
    {
        try {
            $createOrderDto = new CreateOrderDTO($totalPrice,$paymentMethodId,$orderItems);
            return $this->orderRepository->createOrder($createOrderDto);
        } catch (Throwable $e) {
            throw new OrderException('Failed to create new order: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws OrderException
     */
    public function successOrFailStripeOrder(string $sessionId, Order $latestOrder): Order{
        DB::beginTransaction();
        try{
            $sessionInfo = $this->stripeService->retrieveSession($sessionId);
            if($sessionInfo->payment_status != StripePaymentStatusEnum::PAID->value){
                $latestOrder->payment_status= OrderPaymentStatusEnum::FAILED->value;
            }
            else if($sessionInfo->payment_status == StripePaymentStatusEnum::PAID->value){
                $latestOrder->payment_status= OrderPaymentStatusEnum::PAID->value;
            }
            $this->orderRepository->updateOrder($latestOrder);
            DB::commit();
            return $latestOrder;
        }
        catch (Throwable $exception){
            Log::error($exception);
            DB::rollBack();
            throw new OrderException('Something went wrong during stripe order processing!', 0, $exception);
        }
    }

}
