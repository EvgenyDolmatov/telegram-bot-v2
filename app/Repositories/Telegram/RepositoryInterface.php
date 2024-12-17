<?php

namespace App\Repositories\Telegram;

interface RepositoryInterface
{
    public function createDto(?array $data = null): mixed;
}
