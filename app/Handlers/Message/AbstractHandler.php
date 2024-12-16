<?php

namespace App\Handlers\Message;

use App\Models\User;
use App\Repositories\Telegram\AbstractRepository;
use App\Services\TelegramService;

abstract class AbstractHandler
{
    protected User $user;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly AbstractRepository $repository
    ) {
        $this->user = User::getOrCreate($repository);
    }

    abstract public function handle(string $message): void;
}
