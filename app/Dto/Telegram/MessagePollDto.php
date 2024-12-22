<?php

namespace App\Dto\Telegram;

use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\Message\PollDto;

class MessagePollDto
{
    private int $id;

    private FromDto $from;

    private ChatDto $chat;

    private int $date;

    private PollDto $poll;

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

    public function getChat(): ChatDto
    {
        return $this->chat;
    }

    public function setChat(ChatDto $chat): self
    {
        $this->chat = $chat;

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

    public function getPoll(): PollDto
    {
        return $this->poll;
    }

    public function setPoll(PollDto $poll): self
    {
        $this->poll = $poll;

        return $this;
    }
}
