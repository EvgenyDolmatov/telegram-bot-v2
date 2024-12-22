<?php

namespace App\Dto\Telegram\Message\Poll;

class OptionDto
{
    private string $text;
    private int $voterCount;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getVoterCount(): int
    {
        return $this->voterCount;
    }

    public function setVoterCount(int $voterCount): self
    {
        $this->voterCount = $voterCount;

        return $this;
    }
}
