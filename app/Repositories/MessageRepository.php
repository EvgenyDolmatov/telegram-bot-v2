<?php

namespace App\Repositories;

use App\Dto\Message\ChatDto;
use App\Dto\Message\FromDto;
use App\Dto\MessageDto;

class MessageRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function getDto(): MessageDto
    {
        try {
            $data = json_decode($this->response, true)['result'];

            $dto = (new MessageDto())
                ->setId($data['message_id'])
                ->setDate($data['date'])
                ->setFrom($this->getFromDto())
                ->setChat($this->getChatDto())
                ->setText($data['text'] ?? null);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid "message" response');
        }

        return $dto;
    }

    private function getFromDto(): FromDto
    {
        try {
            $data = json_decode($this->response, true)['result']['from'];

            $dto = (new FromDto())
                ->setId($data['id'])
                ->setIsBot($data['is_bot'])
                ->setFirstName($data['first_name'] ?? null)
                ->setLastName($data['last_name'] ?? null)
                ->setUsername($data['username'] ?? null)
                ->setLanguageCode($data['language_code'] ?? null);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid "message from" response');
        }

        return $dto;
    }

    private function getChatDto(): ChatDto
    {
        try {
            $data = json_decode($this->response, true)['result']['chat'];

            $dto = (new ChatDto())
                ->setId($data['id'])
                ->setFirstName($data['first_name'] ?? null)
                ->setLastName($data['last_name'] ?? null)
                ->setUsername($data['username'] ?? null)
                ->setType($data['type']);
        } catch (\Throwable $exception) {
            throw new \Exception('Invalid "message chat" response');
        }

        return $dto;
    }
}
