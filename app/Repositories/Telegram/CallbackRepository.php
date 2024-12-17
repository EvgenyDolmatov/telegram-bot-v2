<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\CallbackQueryDto;
use Illuminate\Http\Request;

readonly class CallbackRepository implements RepositoryInterface
{
    public function __construct(private Request $request) {}

    public function createDto(?array $data = null): CallbackQueryDto
    {
        return new CallbackQueryDto();
    }
}
