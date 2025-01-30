<?php

namespace App\Handlers;

use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class PollAnswerHandler
{
    protected User $user;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
    }

    public function handle(): void
    {
        Log::debug("PollAnswerHandler");
    }
}
