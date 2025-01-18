<?php

namespace App\Builder\Poll;

interface PollBuilderInterface
{
    public function setQuestion(string $question): void;
    public function setOption(string $option): void;
    public function setIsQuiz(bool $isQuiz): void;
    public function setCorrectOptionId(int $correctOptionId): void;
}
