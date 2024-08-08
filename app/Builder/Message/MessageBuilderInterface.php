<?php

namespace App\Builder\Message;

use App\Dto\ButtonDto;

interface MessageBuilderInterface
{
    public function setText(string $text): void;
    public function setButton(ButtonDto $button): void;
}
