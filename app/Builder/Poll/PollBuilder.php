<?php

namespace App\Builder\Poll;

class PollBuilder implements PollBuilderInterface
{
    private Poll $poll;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->poll = new Poll();
    }

    public function setQuestion(string $question): void
    {
        $this->poll->setQuestion($question);
    }

    public function setOption(array $option): void
    {
        $this->poll->setOption($option);
    }

    public function setIsQuiz(bool $isQuiz): void
    {
        $this->poll->setIsQuiz($isQuiz);
    }

    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->poll->setIsAnonymous($isAnonymous);
    }

    public function setCorrectOptionId(int $correctOptionId): void
    {
        $this->poll->setCorrectOptionId($correctOptionId);
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }
}
