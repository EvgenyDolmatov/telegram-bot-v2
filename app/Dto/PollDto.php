<?php

namespace App\Dto;

class PollDto
{
    private string $id;
    private string $question;
    private array $options;
    private int $totalVoterCount;
    private bool $isClosed;
    private bool $isAnonymous;
    private string $type;
    private bool $isAllowsMultipleAnswers;
    private ?int $correctOptionId;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getTotalVoterCount(): int
    {
        return $this->totalVoterCount;
    }

    public function setTotalVoterCount(int $totalVoterCount): self
    {
        $this->totalVoterCount = $totalVoterCount;

        return $this;
    }

    public function getIsClosed(): bool
    {
        return $this->isClosed;
    }

    public function setIsClosed(bool $isClosed): self
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    public function getIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsAllowsMultipleAnswers(): bool
    {
        return $this->isAllowsMultipleAnswers;
    }

    public function setIsAllowsMultipleAnswers(bool $isAllowsMultipleAnswers): self
    {
        $this->isAllowsMultipleAnswers = $isAllowsMultipleAnswers;

        return $this;
    }

    public function getCorrectOptionId(): ?int
    {
        return $this->correctOptionId;
    }

    public function setCorrectOptionId(?int $correctOptionId): self
    {
        $this->correctOptionId = $correctOptionId;

        return $this;
    }
}
