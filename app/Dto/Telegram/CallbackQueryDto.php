<?php

namespace App\Dto\Telegram;


use App\Dto\Telegram\Message\FromDto;

class CallbackQueryDto
{
    private int $id;
    private FromDto $from;
    private MessageTextDto $message;
    private string $chatInstance;
    private string $data;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFrom(): FromDto
    {
        return $this->from;
    }

    public function setFrom(FromDto $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getMessage(): MessageTextDto
    {
        return $this->message;
    }

    public function setMessage(MessageTextDto $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getChatInstance(): string
    {
        return $this->chatInstance;
    }

    public function setChatInstance(string $chatInstance): self
    {
        $this->chatInstance = $chatInstance;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }
}
