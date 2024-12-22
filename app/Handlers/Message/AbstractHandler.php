<?php

namespace App\Handlers\Message;

use App\Models\User;
use App\Repositories\Tg\Request\RepositoryInterface;
use App\Services\TelegramService;

abstract class AbstractHandler
{
    protected User $user;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
    }

    abstract public function handle(string $message): void;
}
