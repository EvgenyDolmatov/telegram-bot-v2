<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Models\TrashMessage;
use Exception;
use Illuminate\Http\Request;

abstract readonly class MessageRepository implements RepositoryInterface
{
    public function __construct(
        protected Request $request
    ) {
    }

    abstract public function createDto(?array $data = null): mixed;

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
        $messageDto = $this->createDto();

        TrashMessage::add(
            chatId: $messageDto->getChat()->getId(),
            messageId: $messageDto->getId(),
            isTrash: $isTrash
        );
    }
}
