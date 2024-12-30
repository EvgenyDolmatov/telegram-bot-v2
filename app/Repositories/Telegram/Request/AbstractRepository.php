<?php

namespace App\Repositories\Telegram\Request;

use App\Dto\Telegram\Message\Component\ChatDto;
use App\Dto\Telegram\Message\Component\FromDto;
use App\Models\TrashMessage;
use Exception;

abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly array $payload
    ) {
    }

    protected function getFromDto(array $data): FromDto
    {
        return (new FromDto())
            ->setId($data['id'])
            ->setIsBot($data['is_bot'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setLanguageCode($data['language_code'] ?? null);
    }

    protected function getChatDto(array $data): ChatDto
    {
        return (new ChatDto())
            ->setId($data['id'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setType($data['type']);
    }

    /**
     * @throws Exception
     */
    public function addToTrash(bool $isTrash = true): void
    {
        $dto = $this->createDto();
        $messageDto = method_exists($dto, 'getMessage') ? $dto->getMessage() : $dto;

        TrashMessage::add(
            chatId: $messageDto->getChat()->getId(),
            messageId: $messageDto->getId(),
            isTrash: $isTrash
        );
    }
}
