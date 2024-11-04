<?php

namespace App\Repositories;

use App\Dto\Poll\OptionDto;
use App\Dto\PollDto;
use Illuminate\Http\Client\Response;

class PollRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function getDto(): PollDto
    {
        try {
            $data = json_decode($this->response, true)['result']['poll'];

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
                ->setCorrectOptionId($data['correct_option_id']);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid poll response');
        }

        return $dto;
    }
}
