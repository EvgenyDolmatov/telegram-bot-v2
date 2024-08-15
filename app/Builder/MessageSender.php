<?php

namespace App\Builder;

use App\Builder\Message\Message;
use App\Builder\Message\MessageBuilderInterface;

class MessageSender
{
    private MessageBuilderInterface $builder;

    public function setBuilder(MessageBuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function createMessage(string $text, ?array $buttons = null): Message
    {
        $builder = $this->getBuilder();
        $builder->setText($text);

        if ($buttons) {
            foreach ($buttons as $button) {
                $builder->setButton($button);
            }
        }

        return $builder->getMessage();
    }

    public function getBuilder(): MessageBuilderInterface
    {
        return $this->builder;
    }
}
