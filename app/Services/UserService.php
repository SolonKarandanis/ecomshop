<?php

namespace App\Services;

use App\Dtos\CreateUserDTO;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository){}

    public function createUser(CreateUserDTO $dto):User{
        $user= $this->userRepository->createUser($dto);
        event(new Registered($user));
        return $user;
    }

    public function getUsersWithOrderedItems(): Collection{
        return $this->userRepository->getUsersWithOrderedItems();
    }
}
