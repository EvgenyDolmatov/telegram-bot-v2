<?php

namespace App\Dto\Telegram\Message;

use App\Dto\Telegram\Message\Component\FromDto;

class PollAnswerDto
{
    private string $id;
    private FromDto $from;
    private array $optionIds;

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

    public function getOptionIds(): array
    {
        return $this->optionIds;
    }

    public function setOptionIds(array $optionIds): self
    {
        $this->optionIds = $optionIds;

        return $this;
    }
}
