<?php

namespace App\Livewire\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

    public function mount($token){
        $this->token = $token;
    }
    public function submit(){
        $validated = $this->validate((new ResetPasswordRequest())->rules());

        $status = Password::reset([
           'email' => $this->email,
           'password' => $this->password,
           'password_confirmation' => $this->password_confirmation,
           'token' => $this->token,
        ], function (User $user, string $password) {
            $password= $this->password;
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET?$this->redirect(route('login')):session()->flash('error','Something went wrong, please try again');
    }
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
