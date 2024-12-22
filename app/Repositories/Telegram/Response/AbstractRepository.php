<?php

namespace App\Repositories\Telegram\Response;

abstract class AbstractRepository
{
    public function __construct(
        protected readonly array $payload
    ) {
    }

    abstract public function createDto();
}
