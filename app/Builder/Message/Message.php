<?php

namespace App\Builder\Message;

use App\Dto\Telegram\Message\Component\ButtonDto;

class Message
{
    private string $text;
    private array $buttons = [];

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setButton(ButtonDto $button): void
    {
        $this->buttons[] = $button;
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }
}
