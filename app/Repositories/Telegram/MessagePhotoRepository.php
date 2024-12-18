<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\Message\PhotoDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Models\TrashMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessagePhotoRepository implements RepositoryInterface
{
    public function __construct(private Request $request) {}

    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessagePhotoDto
    {
        $data = $data ?: $this->request->get("message");

        try {
            $dto = (new MessagePhotoDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setPhoto($this->getPhotoItems($data['photo']));
        } catch (Exception $e) {
            Log::error('Some problem occurred with MessagePhotoRepository.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessagePhotoRepository.');
        }

        return $dto;
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

    private function getFromDto(array $data): FromDto
    {
        return (new FromDto())
            ->setId($data['id'])
            ->setIsBot($data['is_bot'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setLanguageCode($data['language_code'] ?? null);
    }

    private function getChatDto(array $data): ChatDto
    {
        return (new ChatDto())
            ->setId($data['id'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setType($data['type']);
    }

    private function getPhotoItems(array $data): array
    {
        $photoItems = [];

        foreach ($data as $dataItem) {
            $photoItems[] = (new PhotoDto())
                ->setFileId($dataItem['file_id'])
                ->setFileUniqueId($dataItem['file_unique_id'])
                ->setFileSize($dataItem['file_size'])
                ->setWidth($dataItem['width'])
                ->setHeight($dataItem['height']);
        }

        return $photoItems;
    }
}
