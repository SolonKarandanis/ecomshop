<?php

namespace App\Livewire\Auth;

use App\Dtos\CreateUserDTO;
use App\Enums\RolesEnum;
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
    public string $role = RolesEnum::ROLE_BUYER->value;

    protected UserService $userService;

    public function boot(
        UserService $userService
    ): void{
        $this->userService = $userService;
    }

    public function save()
    {
        $request = new RegisterUserRequest();
        $request->merge([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ]);
        $this->validate($request->rules());
        $dto = CreateUserDTO::fromRequest($request);
        $user = null;
        if (config('features.suppliers_enabled') && $this->role === RolesEnum::ROLE_BUYER->value || !config('features.suppliers_enabled')){
            $user = $this->userService->createBuyer($dto);
        }
        else{
            $user = $this->userService->createSupplier($dto);
        }
        auth()->login($user);
        return redirect()->intended('/');
    }
    public function render()
    {
        return view('livewire.auth.register-page', [
            'areSuppliersEnabled' => config('features.suppliers_enabled'),
        ]);
    }
}
