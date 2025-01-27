<?php

namespace App\Enums;

use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\UserState;

interface StateInterface
{
    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState;
    public function sender(RepositoryInterface $repository, TelegramService $telegramService, User $user): SenderInterface;
}
