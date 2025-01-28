<?php

namespace App\Enums;

use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;
use App\States\Gameplay\PollAnswers\GameplayFirstAnswerState;
use App\States\UserState;

enum GameplayEnum: string
{
    case firstQuestion = 'gameplay_first_question';

    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::firstQuestion => new GameplayFirstAnswerState($repository, $telegramService),
        };
    }
}
