<?php

namespace App\Repositories;

use App\Dto\Poll\OptionDto;
use App\Dto\PollDto;
use Illuminate\Http\Client\Response;

class PollRepository
{
    public function __construct(
        private readonly Response $response
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getPollDto(): PollDto
    {
        try {
            $data = json_decode($this->response, true);
            $pollData = $data['result']['poll'];

            $poll = (new PollDto())
                ->setId($pollData['id'])
                ->setQuestion($pollData['question'])
                ->setOptions(array_map(
                    fn($option) => (new OptionDto())
                        ->setText($option['text'])
                        ->setVoterCount($option['voter_count']),
                    $pollData['options']))
                ->setTotalVoterCount($pollData['total_voter_count'])
                ->setIsClosed($pollData['is_closed'])
                ->setIsAnonymous($pollData['is_anonymous'])
                ->setType($pollData['type'])
                ->setIsAllowsMultipleAnswers($pollData['allows_multiple_answers'])
                ->setCorrectOptionId($pollData['correct_option_id']);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid poll response');
        }

        return $poll;
    }
}
