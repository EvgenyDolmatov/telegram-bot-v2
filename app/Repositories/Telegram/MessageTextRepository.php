<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\MessageTextDto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

readonly class MessageTextRepository implements RepositoryInterface
{
    public function __construct(private Request $request) {}

    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessageTextDto
    {
        $data = $data ?: $this->request->get("message");

        try {
            $dto = (new MessageTextDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setText($data['text']);
        } catch (Exception $e) {
            Log::error('Some problem occurred with MessageTextRepository.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessageTextRepository.');
        }

        return $dto;
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
}
