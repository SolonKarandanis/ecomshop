<?php

namespace App\Livewire\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Forgot Password')]
class ForgotPasswordPage extends Component
{

    public string $email = '';
    public function submit(){
        $validated = $this->validate((new ForgotPasswordRequest())->rules());
        $status = Password::sendResetLink(['email' => $validated['email']]);
        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Password reset link has been sent to your email.');
            $this->email='';
        }
    }
    public function render()
    {
        return view('livewire.auth.forgot-password-page');
    }
}
