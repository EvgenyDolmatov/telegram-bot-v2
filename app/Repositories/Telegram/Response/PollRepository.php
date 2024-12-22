<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\Message\Poll\OptionDto;
use App\Dto\Telegram\Message\PollDto;

class PollRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function createDto(): PollDto
    {
        try {
            $data = $this->payload;

            $dto = (new PollDto())
                ->setId($data['id'])
                ->setQuestion($data['question'])
                ->setOptions(array_map(
                    fn($option) => (new OptionDto())
                        ->setText($option['text'])
                        ->setVoterCount($option['voter_count']),
                    $data['options']))
                ->setTotalVoterCount($data['total_voter_count'])
                ->setIsClosed($data['is_closed'])
                ->setIsAnonymous($data['is_anonymous'])
                ->setType($data['type'])
                ->setIsAllowsMultipleAnswers($data['allows_multiple_answers'])
                ->setCorrectOptionId($data['correct_option_id'] ?? null);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid poll response');
        }

        return $dto;
    }
}
