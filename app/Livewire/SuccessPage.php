<?php

namespace App\Livewire;

use App\Services\OrderService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Stripe\Exception\ApiErrorException;

#[Title('Order Success')]
class SuccessPage extends Component
{
    #[Url]
    public $session_id;
    protected OrderService $orderService;

    public function boot(
        OrderService $orderService,
    ): void{
        $this->orderService = $orderService;
    }

    /**
     * @throws ApiErrorException
     * @throws \Throwable
     */
    public function render()
    {
        $userId = auth()->user()->id;
        $latestOrder = $this->orderService->getUsersLatestOrder($userId);
        if(!empty($this->session_id)) {
            $latestOrder = $this->orderService->successOrFailStripeOrder($this->session_id,$latestOrder);
            if(is_null($latestOrder)) {
                return redirect()->route('cancel');
            }
        }
        return view('livewire.success-page',['order'=>$latestOrder]);
    }
}
