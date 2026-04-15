<?php

namespace App\Services;

use App\Dtos\CreateUserDTO;
use App\Dtos\ResetPasswordDTO;
use App\Enums\RolesEnum;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
    ){}

    public function createUser(CreateUserDTO $dto):User{
        $user= $this->userRepository->createUser($dto);
        event(new Registered($user));
        return $user;
    }

    public function createBuyer(CreateUserDTO $dto):User{
        $user= $this->createUser($dto);
        $buyerRole=$this->roleRepository->getBuyerRole();
        $user->assignRole($buyerRole);
        return $user;
    }

    public function getUsersWithOrderedItems(): Collection{
        return $this->userRepository->getUsersWithOrderedItems();
    }

    public function resetPassword(ResetPasswordDTO $dto):string{
        return Password::reset([
            'email' => $dto->getEmail(),
            'password' => $dto->getPassword(),
            'password_confirmation' => $dto->getPasswordConfirmation(),
            'token' => $dto->getToken(),
        ], function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));
            $this->userRepository->saveUser($user);
            event(new PasswordReset($user));
        });
    }
}
