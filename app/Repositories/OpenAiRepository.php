<?php

namespace App\Repositories;

use App\Dto\OpenAi\OpenAiQuestionDto;
use App\Dto\OpenAi\OpenAiUsageDto;
use App\Dto\OpenAiCompletionDto;
use App\Services\OpenAiService;
use Illuminate\Support\Facades\Log;

readonly class OpenAiRepository
{
    public function __construct(
        private OpenAiService $service
    ) {}

    public function getCompletion(): OpenAiCompletionDto
    {
        $responseArray = json_decode($this->service->getCompletions(), true);

        try {
            if ($responseArray['object'] === 'chat.completion') {
                $openAiUsage = new OpenAiUsageDto(
                    promptTokens: $responseArray['usage']['prompt_tokens'],
                    completionTokens: $responseArray['usage']['completion_tokens'],
                    totalTokens: $responseArray['usage']['total_tokens']
                );

                $openAiCompletionDto = new OpenAiCompletionDto(
                    id: $responseArray['id'],
                    object: $responseArray['object'],
                    createdAt: $responseArray['created'],
                    model: $responseArray['model'],
                    questions: $this->getQuestions($responseArray['choices'][0]['message']['content']),
                    usage: $openAiUsage
                );
            }
        } catch (\Throwable $exception) {
            Log::error(
                'Incorrect API key for Open AI.',
                ['message' => $exception->getMessage()]
            );
        }

        return $openAiCompletionDto;
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
