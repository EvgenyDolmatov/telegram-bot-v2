<?php

namespace App\Repositories;

use App\Dto\OpenAi\OpenAiQuestionDto;
use App\Dto\OpenAi\OpenAiUsageDto;
use App\Dto\OpenAiCompletionDto;
use App\Services\OpenAiService;

readonly class OpenAiRepository
{
    public function __construct(
        private OpenAiService $service
    ) {
    }

    public function getCompletion(): ?OpenAiCompletionDto
    {
        $responseArray = json_decode($this->service->getCompletions(), true);

        if (isset($responseArray['object']) && $responseArray['object'] === 'chat.completion') {
            $openAiUsage = new OpenAiUsageDto(
                promptTokens: $responseArray['usage']['prompt_tokens'],
                completionTokens: $responseArray['usage']['completion_tokens'],
                totalTokens: $responseArray['usage']['total_tokens']
            );

            if (!isset($responseArray['choices'][0]['message']['content'])) {
                return null;
            }

            return new OpenAiCompletionDto(
                id: $responseArray['id'],
                object: $responseArray['object'],
                createdAt: $responseArray['created'],
                model: $responseArray['model'],
                questions: $this->getQuestions($responseArray['choices'][0]['message']['content']),
                usage: $openAiUsage
            );
        }

        return null;
    }

    public function getQuestions(string $content): array
    {
        return array_map(fn($question) => new OpenAiQuestionDto(
            text: $question['question_text'],
            options: $question['options'],
            answer: $question['correct_answer'] ?? null
        ), json_decode($content, true));
    }
}
