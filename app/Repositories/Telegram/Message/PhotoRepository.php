<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\MessagePhotoDto;
use App\Exceptions\ResponseException;
use App\Repositories\Telegram\AbstractRepository;
use Throwable;

final readonly class PhotoRepository extends AbstractRepository
{
    /**
     * @throws ResponseException
     */
    public function getDto(array $data = null): MessagePhotoDto
    {
        try {
            $dto = (new MessagePhotoDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setPhoto($data['photo'])
                ->setCaption($data['caption'] ?? null);

            if (array_key_exists('reply_markup', $data)) {
                $dto->setButtons($this->getButtons($data['reply_markup']['inline_keyboard']));
            }
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $dto;
    }
}
