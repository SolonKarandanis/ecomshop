<?php

namespace App\Livewire;

use App\Dtos\ChangePasswordDto;
use App\Dtos\UpdateProfileDto;
use App\Enums\MessageSeverityEnum;
use App\Exceptions\ProfileException;
use App\Services\UiService;
use App\Services\UserService;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Profile')]
class ProfilePage extends Component
{
    public string $name  = '';
    public string $email = '';

    public string $currentPassword         = '';
    public string $newPassword             = '';
    public string $newPasswordConfirmation = '';

    protected UserService    $userService;
    protected UiService      $uiService;

    public function boot(
        UserService    $userService,
        UiService      $uiService,
    ): void {
        $this->userService    = $userService;
        $this->uiService      = $uiService;
    }

    public function mount(): void
    {
        $this->name  = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        try {
            $dto = UpdateProfileDto::fromArray([
                'name'  => $this->name,
                'email' => $this->email,
            ]);
            $this->userService->updateProfile(auth()->user(), $dto);
            $this->uiService->showMessage(
                MessageSeverityEnum::SUCCESS,
                __('messages.update_profile.title'),
                __('messages.update_profile.success')
            );
        } catch (ProfileException $e) {
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                __('messages.update_profile.title'),
                $e->getMessage()
            );
        }
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword'         => 'required|string',
            'newPassword'             => 'required|string|min:8|same:newPasswordConfirmation',
            'newPasswordConfirmation' => 'required|string',
        ]);

        try {
            $dto = ChangePasswordDto::fromArray([
                'currentPassword'         => $this->currentPassword,
                'newPassword'             => $this->newPassword,
                'newPasswordConfirmation' => $this->newPasswordConfirmation,
            ]);
            $this->userService->changePassword(auth()->user(), $dto);
            $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
            $this->uiService->showMessage(
                MessageSeverityEnum::SUCCESS,
                __('messages.change_password.title'),
                __('messages.change_password.success')
            );
        } catch (ProfileException $e) {
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                __('messages.change_password.title'),
                $e->getMessage()
            );
        }
    }

    public function render(): View
    {
        $user = $this->userService->getUserWithAddresses(auth()->id());
        return view('livewire.profile-page', compact('user'));
    }
}
