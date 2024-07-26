<?php

namespace App\Builder;

use App\Builder\Message\Message;
use App\Builder\Message\MessageBuilderInterface;
use Illuminate\Support\Facades\Http;

class Sender
{
    private MessageBuilderInterface $builder;

    public function setBuilder(MessageBuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function createSimpleMessage(string $text): Message
    {
        $builder = $this->getBuilder();
        $builder->setText($text);

        return $builder->getMessage();
    }

    public function createMessageWithButtons(string $text, array $buttons): Message
    {
        $builder = $this->getBuilder();
        $builder->setText($text);

        foreach ($buttons as $button) {
            $builder->setButton($button);
        }

        return $builder->getMessage();
    }

    public function getBuilder(): MessageBuilderInterface
    {
        return $this->builder;
    }
}
