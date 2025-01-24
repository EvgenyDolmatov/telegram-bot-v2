<?php

namespace App\Enums\Callback;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\StateEnum;

enum PollEnum: string
{
    case TypeQuiz = 'poll_type_quiz';
    case TypeSurvey = 'poll_type_survey';
    case RepeatFlow = 'poll_repeat_flow';
    case AfterAiRespondedMenu = 'poll_after_ai_responded_menu';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::TypeQuiz,
            self::TypeSurvey => StateEnum::PollThemeChoice,
            self::RepeatFlow => StateEnum::PollAiRespondedChoice,
            self::AfterAiRespondedMenu => StateEnum::PollAfterAiRespondedChoice,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            self::TypeQuiz => "Викторина",
            self::TypeSurvey => "Опрос",
            self::RepeatFlow => "🔄 Создать еще 5 вопросов",
            self::AfterAiRespondedMenu => "🎲 Завершить",
        };
    }

    public function getButtonDto(?string $text = null): ButtonDto
    {
        return new ButtonDto($this->value, $text ?? $this->buttonText());
    }
}
