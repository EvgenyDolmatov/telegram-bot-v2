<?php

namespace App\Repositories\Telegram\Request;

use App\Dto\Telegram\CallbackQueryDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use Exception;

class CallbackRepository extends AbstractRepository
{
    private MessageRepository $messageRepository;

    public function __construct(array $payload)
    {
        parent::__construct($payload);
        $this->messageRepository = new MessageRepository($payload);
    }

    /**
     * @throws Exception
     */
    public function createDto(): CallbackQueryDto
    {
        $data = $this->payload;

        return (new CallbackQueryDto())
            ->setId($data['id'])
            ->setFrom($this->getFromDto($data['from']))
            ->setMessage($this->getMessageDto($data['message']))
            ->setChatInstance($data['chat_instance'])
            ->setData($data['data']);
    }

    /**
     * @throws Exception
     */
    private function getMessageDto(array $data): MessageTextDto|MessagePhotoDto
    {
        return $this->messageRepository->createDto($data);
    }
}
