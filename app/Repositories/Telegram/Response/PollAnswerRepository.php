<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\Message\PollAnswerDto;

class PollAnswerRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function createDto(): PollAnswerDto
    {
        try {
            $data = $this->payload;

            $dto = (new PollAnswerDto())
                ->setId($data['poll_id'])
                ->setFrom($this->getFromDto($data['user']))
                ->setOptionIds($data['option_ids']);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid poll answer response');
        }

        return $dto;
    }
}
