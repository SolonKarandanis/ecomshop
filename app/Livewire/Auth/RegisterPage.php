<?php

namespace App\Livewire\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function save()
    {
        $validated = $this->validate((new RegisterUserRequest())->rules());

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        auth()->login($user);

        return redirect()->intended('/');
    }
    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
