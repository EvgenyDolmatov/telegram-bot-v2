<?php

namespace App\Builder\Message;

use App\Dto\Telegram\Message\Component\ButtonDto;

interface MessageBuilderInterface
{
    public function setText(string $text): void;
    public function setButton(ButtonDto $button): void;
}
