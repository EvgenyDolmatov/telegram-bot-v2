<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\CallbackQueryDto;

class CallbackRepository implements RepositoryInterface
{
    public function createDto(?array $data = null): CallbackQueryDto
    {
        return new CallbackQueryDto();
    }
}
