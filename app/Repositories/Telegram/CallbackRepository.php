<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\CallbackQueryDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use App\Models\TrashMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

readonly class CallbackRepository implements RepositoryInterface
{
    public function __construct(private Request $request) {}

    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): CallbackQueryDto
    {
        $data = $data ?: $this->request->get("callback_query");

        try {
            $dto = (new CallbackQueryDto())
                ->setId($data['id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setMessage($this->getMessageDto($data['message']))
                ->setChatInstance($data['chat_instance'])
                ->setData($data['data']);
        } catch (Exception $e) {
            Log::error('Some problem occurred with CallbackRepository.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessageTextRepository.');
        }

        return $dto;
    }

    /**
     * @throws Exception
     */
    public function addToTrash(bool $isTrash = true): void
    {
        $messageDto = $this->createDto()->getMessage();

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

    /**
     * @throws Exception
     */
    private function getMessageDto(array $data): MessageTextDto|MessagePhotoDto
    {
        if (array_key_exists('text', $data)) {
            return (new MessageTextRepository($this->request))->createDto($data);
        }

        if (array_key_exists('photo', $data)) {
            return (new MessagePhotoRepository($this->request))->createDto($data);
        }

        throw new Exception('Unavailable to create message DTO in CallbackRepository.');
    }
}
