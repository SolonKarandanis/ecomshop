<?php

namespace App\Livewire\Auth;

use App\Dtos\CreateUserDTO;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    protected UserRepository $userRepository;

    public function boot(
        UserRepository $userRepository
    ): void{
        $this->userRepository = $userRepository;
    }

    public function save()
    {
        $validated = $this->validate((new RegisterUserRequest())->rules());
        $dto = CreateUserDTO::fromArray($validated);
        $user = $this->userRepository->createUser($dto);
        event(new Registered($user));
        auth()->login($user);
        return redirect()->intended('/');
    }
    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
