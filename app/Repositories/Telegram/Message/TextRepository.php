<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\MessageTextDto;
use App\Exceptions\ResponseException;
use App\Repositories\Telegram\AbstractRepository;
use Throwable;

final readonly class TextRepository extends AbstractRepository
{
    /**
     * @throws ResponseException
     */
    public function getDto(array $data = null): MessageTextDto
    {
        try {
            $dto = (new MessageTextDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setText($data['text']);

            if (array_key_exists('reply_markup', $data)) {
                $dto->setButtons($this->getButtons($data['reply_markup']['inline_keyboard']));
            }
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $dto;
    }
}
