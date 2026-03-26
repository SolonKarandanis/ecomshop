<?php

namespace App\Livewire\Auth;

use App\Dtos\ResetPasswordDTO;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Reset Password')]
class ResetPasswordPage extends Component
{

    public string $password = '';
    public string $password_confirmation = '';
    #[Url]
    public string $email;
    public string $token = '';

    protected UserService $userService;
    public function boot(
        UserService $userService
    ): void{
        $this->userService = $userService;
    }

    public function mount($token){
        $this->token = $token;
    }
    public function submit(){
        $validated = $this->validate((new ResetPasswordRequest())->rules());
        $dto = ResetPasswordDTO::fromArray($validated);
        $status = $this->userService->resetPassword($dto);
        $status === Password::PASSWORD_RESET?$this->redirect(route('login')):session()->flash('error','Something went wrong, please try again');
    }
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
