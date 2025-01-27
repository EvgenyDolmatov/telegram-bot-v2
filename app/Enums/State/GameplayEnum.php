<?php

namespace App\Enums\State;

use App\Enums\StateInterface;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\Gameplay\GameplayCountdownShowSender;
use App\Senders\Gameplay\GameplayWaitingToStartSender;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\Gameplay\GameplayCountdownShowState;
use App\States\Gameplay\GameplayWaitingToStartState;
use App\States\UserState;

enum GameplayEnum: string implements StateInterface
{
    case WaitingToStart = 'gameplay_waiting_to_start';
    case CountdownShow = 'gameplay_countdown_show';

    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::WaitingToStart => new GameplayWaitingToStartState($repository, $telegramService),
            self::CountdownShow => new GameplayCountdownShowState($repository, $telegramService),
        };
    }

    public function sender(RepositoryInterface $repository, TelegramService $telegramService, User $user): SenderInterface
    {
        return match ($this) {
            self::WaitingToStart => new GameplayWaitingToStartSender($repository, $telegramService, $user),
            self::CountdownShow => new GameplayCountdownShowSender($repository, $telegramService, $user),
        };
    }
}
