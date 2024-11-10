<?php

namespace App\Dto;

class User1Dto
{
    private int $id;
    private ?string $username;
    private bool $isBot;
    private ?string $firstName;
    private ?string $lastName;

    public function __construct(
        int $id,
        ?string $username = null,
        bool $isBot = false,
        ?string $firstName = null,
        ?string $lastName = null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->isBot = $isBot;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getIsBot(): bool
    {
        return $this->isBot;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
}
