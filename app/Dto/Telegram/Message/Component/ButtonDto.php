<?php

namespace App\Dto\Telegram\Message\Component;

class ButtonDto
{
    private string $callbackData;
    private string $text;
    private ?string $url;

    public function __construct(string $callbackData, string $text, ?string $url = null)
    {
        $this->callbackData = $callbackData;
        $this->text = $text;
        $this->url = $url;
    }

    public function getCallbackData(): string
    {
        return $this->callbackData;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
