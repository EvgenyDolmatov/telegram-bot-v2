<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\CallbackQueryDto;
use App\Exceptions\ResponseException;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class CallbackQueryRepository extends AbstractRepository
{
    /**
     * @throws ResponseException
     */
    public function getDto(): CallbackQueryDto
    {
        try {
            $payload = $this->request->all();

            Log::debug('CallbackQueryRepository: ' . json_encode($payload));

            /**
             * @var array{
             *     id: string,
             *     from: array,
             *     message: array,
             *     chat_instance: string,
             *     data: string
             * } $data
             */
            $data = $payload['callback_query'];

            $dto = (new CallbackQueryDto())
                ->setId($data['id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setMessage($this->getMessageDto($data['message']))
                ->setChatInstance($data['chat_instance'])
                ->setData($data['data']);
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $dto;
    }
}
