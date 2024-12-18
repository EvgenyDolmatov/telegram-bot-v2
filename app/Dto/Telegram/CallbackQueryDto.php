<?php

namespace App\Dto\Telegram;


use App\Dto\Telegram\Message\FromDto;

class CallbackQueryDto
{
    private string $id;
    private FromDto $from;
    private MessageTextDto|MessagePhotoDto $message;
    private string $chatInstance;
    private string $data;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
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

    public function getMessage(): MessageTextDto|MessagePhotoDto
    {
        return $this->message;
    }

    public function setMessage(MessageTextDto|MessagePhotoDto $message): self
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
