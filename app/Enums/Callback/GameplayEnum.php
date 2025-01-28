<?php

namespace App\Enums\Callback;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\StateEnum;

enum GameplayEnum: string
{
    case Start = 'gameplay_start';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::Start => StateEnum::GameplayCountdownShow,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            self::Start => "Пройти тест",
        };
    }

    public function getButtonDto(?string $value = null, ?string $text = null): ButtonDto
    {
        return new ButtonDto(
            callbackData: $value ?? $this->value,
            text: $text ?? $this->buttonText()
        );
    }
}
