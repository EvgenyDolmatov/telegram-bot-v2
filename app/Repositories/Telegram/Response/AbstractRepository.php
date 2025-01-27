<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\Message\Component\FromDto;
use App\Repositories\Telegram\Request\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly array $payload
    ) {
    }

    abstract public function createDto(): mixed;

    protected function getFromDto(array $data): FromDto
    {
        return (new FromDto())
            ->setId($data['id'])
            ->setIsBot($data['is_bot'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setLanguageCode($data['language_code'] ?? null);
    }
}
