<?php

namespace App\Dto;

class ChatDto
{
    private int $id;
    private ?string $username;
    private string $type;
    private ?string $firstName;
    private ?string $lastName;

    public function __construct(
        int $id,
        string $username = null,
        string $type = 'private',
        ?string $firstName = null,
        ?string $lastName = null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->type = $type;
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

    public function getType(): string
    {
        return $this->type;
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
