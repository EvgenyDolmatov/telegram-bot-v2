<?php

namespace App\Repositories\Telegram\Response;

use App\Repositories\Telegram\Request\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly array $payload
    ) {
    }

    abstract public function createDto(): mixed;
}
