<?php

namespace App\Dtos;

class UpdateProfileDto
{
    private string $name;
    private string $email;

    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->setName($data['name']);
        $instance->setEmail($data['email']);
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
}
