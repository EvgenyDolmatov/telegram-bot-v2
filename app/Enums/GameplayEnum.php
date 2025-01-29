<?php

namespace App\Enums;

use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\Gameplay\PollAnswers\GameplayQuizModeSender;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\Gameplay\PollAnswers\GameplayQuizModeState;
use App\States\UserState;

enum GameplayEnum: string
{
    case QuizMode = 'gameplay_quiz_mode';

    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::QuizMode => new GameplayQuizModeState($repository, $telegramService),
        };
    }

    public function sender(RepositoryInterface $repository, TelegramService $telegramService, User $user): SenderInterface
    {
        return match ($this) {
            self::QuizMode => new GameplayQuizModeSender($repository, $telegramService, $user),
        };
    }
}
