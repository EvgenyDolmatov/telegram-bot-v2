<?php

namespace App\Repositories;

use Illuminate\Http\Client\Response;

abstract class AbstractRepository
{
    public function __construct(
        protected readonly Response $response
    ) {
    }

    abstract public function getDto(): mixed;
}
