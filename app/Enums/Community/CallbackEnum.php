<?php

namespace App\Enums\Community;

enum CallbackEnum: string
{
    case GameJoinUserToQuiz = 'game_join_user_to_quiz';

    public function toState(): CommunityEnum
    {
        return match ($this) {
            self::GameJoinUserToQuiz => CommunityEnum::GamePlayerWaiting
        };
    }
}
