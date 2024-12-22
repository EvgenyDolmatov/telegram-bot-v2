<?php

namespace App\Repositories\Tg\Response;

abstract class AbstractRepository
{
    public function __construct(
        protected readonly array $payload
    ) {
    }

    abstract public function createDto();
}
