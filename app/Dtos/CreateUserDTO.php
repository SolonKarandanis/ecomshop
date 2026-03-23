<?php

namespace App\Dtos;

class CreateUserDTO
{
    private string $name;
    private string $email;
    private string $password;

    public static function fromArray(array $data): self{
        $instance = new self();
        $instance->setName($data['name']);
        $instance->setEmail($data['email']);
        $instance->setPassword($data['password']);
        return $instance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
