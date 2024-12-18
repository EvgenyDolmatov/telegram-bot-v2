<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\MessageTextDto;
use App\Repositories\Telegram\MessageRepository;
use Exception;
use Illuminate\Support\Facades\Log;

readonly class MessageTextRepository extends MessageRepository
{
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
}
