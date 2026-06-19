<?php

namespace App\Livewire\Partials;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    protected CartService $cartService;
    public int $total_cart_items = 0;
    public int $unread_notifications_count = 0;
    public bool $dropdown_open = false;
    public array $dropdown_notifications = [];

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    public function mount(): void
    {
        $this->total_cart_items = $this->cartService->getCartItemsCount();
        $this->refreshUnreadCount();
    }

    #[On('cartUpdated')]
    public function cartUpdated(): void
    {
        $this->total_cart_items = $this->cartService->getCartItemsCount();
    }

    #[On('notificationReceived')]
    public function notificationReceived(): void
    {
        $this->refreshUnreadCount();
    }

    public function openDropdown(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $notifications = $user->unreadNotifications()->latest()->take(5)->get();
        $this->dropdown_notifications = $notifications->map(fn ($n) => [
            'message'   => $n->data['message'],
            'order_url' => $n->data['order_url'],
            'id'        => $n->id,
        ])->all();

        $notifications->markAsRead();
        $this->unread_notifications_count = $user->unreadNotifications()->count();
        $this->dropdown_open = true;
    }

    public function closeDropdown(): void
    {
        $this->dropdown_open = false;
        $this->dropdown_notifications = [];
    }

    private function refreshUnreadCount(): void
    {
        $user = auth()->user();
        $this->unread_notifications_count = $user
            ? $user->unreadNotifications()->count()
            : 0;
    }

    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
