<?php

namespace App\Builder\Poll;

class Poll
{
    private string $question;
    private array $options = [];
    private bool $isQuiz = false;
    private bool $isAnonymous = false;
    private ?int $correctOptionId = null;

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setOption(string $option): void
    {
        $this->options[] = ['text' => $option];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setIsQuiz(bool $isQuiz): void
    {
        $this->isQuiz = $isQuiz;
    }

    public function getIsQuiz(): bool
    {
        return $this->isQuiz;
    }

    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = $isAnonymous;
    }

    public function getIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setCorrectOptionId(int $correctOptionId): void
    {
        $this->correctOptionId = $correctOptionId;
    }

    public function getCorrectOptionId(): int
    {
        return $this->correctOptionId;
    }
}
