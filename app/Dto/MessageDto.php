<?php

namespace App\Dto;

use App\Dto\Message\FromDto;
use App\Dto\Message\ChatDto;

class MessageDto
{
    private int $id;
    private int $date;
    private FromDto $from;
    private ChatDto $chat;
    private array $photo;
    private ?string $text;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): self
    {
        $this->date = $date;

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

    public function getChat(): ChatDto
    {
        return $this->chat;
    }

    public function setChat(ChatDto $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getPhoto(): array
    {
        return $this->photo;
    }

    public function setPhoto(array $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
