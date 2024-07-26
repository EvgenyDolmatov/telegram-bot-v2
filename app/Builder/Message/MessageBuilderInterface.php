<?php

namespace App\Builder\Message;

interface MessageBuilderInterface
{
    public function setText(string $text): void;
    public function setButton(array $button): void;
}
