<?php

namespace App\Dto\OpenAi;

class OpenAiQuestionDto
{
    private string $text;
    private array $options;
    private ?string $answer;

    public function __construct(string $text, array $options, ?string $answer = null)
    {
        $this->text = $text;
        $this->options = $options;
        $this->answer = $answer;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }
}
