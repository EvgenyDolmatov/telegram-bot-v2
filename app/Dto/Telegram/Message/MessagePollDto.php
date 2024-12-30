<?php

namespace App\Dto\Telegram\Message;

use App\Dto\Telegram\Message\Component\PollDto;
use App\Dto\Telegram\MessageDto;

class MessagePollDto extends MessageDto
{
    private PollDto $poll;

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
