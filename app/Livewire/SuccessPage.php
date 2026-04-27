<?php

namespace App\Livewire;

use App\Enums\MessageSeverityEnum;
use App\Services\OrderService;
use App\Services\UiService;
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
    protected UiService $uiService;

    public function boot(
        OrderService $orderService,
        UiService $uiService
    ): void{
        $this->orderService = $orderService;
        $this->uiService = $uiService;
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

    protected function handleActionResult(bool $result,string|null $dispatchEvent,string $msgTitle,string $msgSuccess,string $msgFail):void
    {
        if($result){
            if($dispatchEvent){
                $this->dispatch($dispatchEvent);
            }
            $this->uiService->showMessage(
                MessageSeverityEnum::SUCCESS,
                $msgTitle,
                $msgSuccess
            );
        }
        else{
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                $msgTitle,
                $msgFail
            );
        }
    }
}
