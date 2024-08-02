<?php

namespace App\Builder;

use App\Builder\Poll\Poll;
use App\Builder\Poll\PollBuilderInterface;

class PollSender
{
    private PollBuilderInterface $builder;

    public function setBuilder(PollBuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function createPoll(
        string $question,
        array  $options,
        bool   $isAnonymous
    ): Poll
    {
        $builder = $this->getBuilder();
        $builder->setQuestion($question);
        $builder->setIsAnonymous($isAnonymous);
        $builder->setIsQuiz(false);

        array_map(fn($option) => $builder->setOption($option), $options);

        return $builder->getPoll();
    }

    public function createQuiz(
        string $question,
        array  $options,
        bool   $isAnonymous,
        int    $correctOptionId
    ): Poll
    {
        $builder = $this->getBuilder();
        $builder->setQuestion($question);
        $builder->setIsAnonymous($isAnonymous);
        $builder->setIsQuiz(true);
        $builder->setCorrectOptionId($correctOptionId);

        array_map(fn($option) => $builder->setOption($option), $options);

        return $builder->getPoll();
    }

    public function getBuilder(): PollBuilderInterface
    {
        return $this->builder;
    }
}
