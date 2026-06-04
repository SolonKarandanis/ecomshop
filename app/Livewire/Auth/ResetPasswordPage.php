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
    public string $email = '';
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
        $request = new ResetPasswordRequest();
        $request->merge([
            'email'                 => $this->email,
            'password'              => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token'                 => $this->token,
        ]);
        $this->validate($request->rules());
        $dto = ResetPasswordDTO::fromRequest($request);
        $status = $this->userService->resetPassword($dto);
        $status === Password::PASSWORD_RESET?$this->redirect(route('login')):session()->flash('error','Something went wrong, please try again');
    }
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
