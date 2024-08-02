<?php

namespace App\Dto;

use App\Dto\OpenAi\OpenAiUsageDto;

class OpenAiCompletionDto
{
    private string $id;
    private string $object;
    private int $createdAt;
    private string $model;
    private array $questions = [];
    private OpenAiUsageDto $usage;

    public function __construct(
        string $id,
        string $object,
        int $createdAt,
        string $model,
        array $questions,
        OpenAiUsageDto $usage
    ) {
        $this->id = $id;
        $this->object = $object;
        $this->createdAt = $createdAt;
        $this->model = $model;
        $this->questions = $questions;
        $this->usage = $usage;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getUsage(): OpenAiUsageDto
    {
        return $this->usage;
    }
}
