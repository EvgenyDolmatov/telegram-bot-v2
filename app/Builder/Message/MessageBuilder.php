<?php

namespace App\Builder\Message;

class MessageBuilder implements MessageBuilderInterface
{
    private Message $message;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->message = new Message();
    }

    public function setText(string $text): void
    {
        $this->message->setText($text);
    }

    public function setButton(array $button): void
    {
        $this->message->setButton($button);
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
