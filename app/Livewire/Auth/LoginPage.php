<?php

namespace App\Livewire\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Login')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public function performLogin(){
        $validated = $this->validate((new LoginRequest())->rules());
        if(!auth()->attempt(['email' => $validated['email'], 'password' => $validated['password']])){
            session()->flash('error', 'Invalid Credentials');
            return;
        }
        return redirect()->intended();
    }
    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
