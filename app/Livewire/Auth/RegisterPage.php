<?php

namespace App\Livewire\Auth;

use App\Dtos\CreateUserDTO;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\UserService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    protected UserService $userService;

    public function boot(
        UserService $userService
    ): void{
        $this->userService = $userService;
    }

    public function save()
    {
        $validated = $this->validate((new RegisterUserRequest())->rules());
        $dto = CreateUserDTO::fromArray($validated);
        $user = $this->userService->createBuyer($dto);
        auth()->login($user);
        return redirect()->intended('/');
    }
    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
