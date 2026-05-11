<?php

namespace App\Livewire;

use App\Enums\MessageSeverityEnum;
use App\Exceptions\OrderException;
use App\Services\OrderService;
use App\Services\UiService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Order Success')]
class SuccessPage extends Component
{
    #[Url]
    public $session_id;
    protected OrderService $orderService;
    protected UiService $uiService;

    public function boot(
        OrderService $orderService,
        UiService $uiService
    ): void{
        $this->orderService = $orderService;
        $this->uiService = $uiService;
    }

    public function render()
    {
        $userId = auth()->user()->id;
        $latestOrder = $this->orderService->getUsersLatestOrder($userId);
        if(!empty($this->session_id)) {
            try {
                $latestOrder = $this->orderService->successOrFailStripeOrder($this->session_id, $latestOrder);
                $title = __('messages.order_paid.title');
                $success = __('messages.order_paid.success');
                $this->uiService->showMessage(MessageSeverityEnum::SUCCESS, $title, $success);
            } catch (OrderException $e) {
                $title = __('messages.order_paid.title');
                $error = __('messages.order_paid.error');
                $this->uiService->showMessage(MessageSeverityEnum::ERROR, $title, $error);
                return redirect()->route('cancel');
            }
        }
        return view('livewire.success-page',['order'=>$latestOrder]);
    }
}
