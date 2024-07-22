<?php

namespace App\Dto;

class ButtonDto
{
    private string $callbackData;
    private string $text;

    public function __construct(string $callbackData, string $text)
    {
        $this->callbackData = $callbackData;
        $this->text = $text;
    }

    public function getCallbackData(): string
    {
        return $this->callbackData;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
