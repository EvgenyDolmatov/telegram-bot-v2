<?php

namespace App\Dto\OpenAi;

class OpenAiUsageDto
{
    private int $promptTokens;
    private int $completionTokens;
    private int $totalTokens;

    public function __construct(
        int $promptTokens,
        int $completionTokens,
        int $totalTokens
    ) {
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
        $this->totalTokens = $totalTokens;
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }
}
