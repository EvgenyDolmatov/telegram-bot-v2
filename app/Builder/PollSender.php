<?php

namespace App\Builder;

use App\Builder\Poll\Poll;
use App\Builder\Poll\PollBuilderInterface;
use Illuminate\Support\Facades\Log;

class PollSender
{
    private PollBuilderInterface $builder;
    private const array OPTION_INDEXES = [
        'a' => 0,
        'b' => 1,
        'c' => 2,
        'd' => 3,
    ];

    public function setBuilder(PollBuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function createPoll(
        string  $question,
        array   $options,
        bool    $isQuiz,
        ?string $correctOptionId = null
    ): Poll
    {
        $builder = $this->getBuilder();
        $builder->setQuestion($question);
        $builder->setIsQuiz($isQuiz);

        if ($correctOptionId) {
            $builder->setCorrectOptionId(self::OPTION_INDEXES[$correctOptionId]);
        }

        array_map(fn($option) => $builder->setOption($option), $options);

        return $builder->getPoll();
    }

    public function getBuilder(): PollBuilderInterface
    {
        return $this->builder;
    }
}
