<?php

namespace App\Repositories\Telegram;

use App\Dto\ButtonDto;
use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use App\Exceptions\ResponseException;
use App\Repositories\Telegram\Message\PhotoRepository;
use App\Repositories\Telegram\Message\TextRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract readonly class AbstractRepository
{
    public function __construct(
        protected Request $request
    ) {
    }

    abstract public function getDto(): mixed;

    /**
     * @throws Exception
     */
    protected function getMessageDto(array $data = null): MessageTextDto|MessagePhotoDto
    {
        $data = $data ?: $this->request->all()['message'];

        if (array_key_exists('text', $data)) {
            $repository = (new TextRepository($this->request));
        }

        if (array_key_exists('photo', $data)) {
            $repository = (new PhotoRepository($this->request));
        }

        if (!isset($repository)) {
            throw new Exception('Message payload must have  text or photo key.');
        }

        return $repository->getDto($data);
    }

    /**
     * @param array{
     *     id: int,
     *     is_bot: bool,
     *     first_name: ?string,
     *     last_name: ?string,
     *     username: ?string,
     *     language_code: ?string
     * } $data
     * @return FromDto
     * @throws ResponseException
     */
    protected function getFromDto(array $data): FromDto
    {
        try {
            $dto = (new FromDto())
                ->setId($data['id'])
                ->setIsBot($data['is_bot'])
                ->setFirstName($data['first_name'] ?? null)
                ->setLastName($data['last_name'] ?? null)
                ->setUsername($data['username'] ?? null)
                ->setLanguageCode($data['language_code'] ?? null);
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $dto;
    }

    /**
     * @param array{
     *     id: int,
     *     first_name: ?string,
     *     last_name: ?string,
     *     username: ?string,
     *     type: string
     * } $data
     * @return ChatDto
     * @throws ResponseException
     */
    protected function getChatDto(array $data): ChatDto
    {
        try {
            $dto = (new ChatDto())
                ->setId($data['id'])
                ->setFirstName($data['first_name'])
                ->setLastName($data['last_name'])
                ->setUsername($data['username'])
                ->setType($data['type']);


        } catch (Throwable $e) {
            Log::debug($e->getMessage());
            throw new ResponseException($e->getMessage());
        }

        return $dto;
    }

    /**
     * @param array{
     *     inline_keyboard: array
     * } $data
     * @return ButtonDto[]
     * @throws ResponseException
     */
    protected function getButtons(array $data): array
    {
        try {
            $buttons = [];
            foreach ($data as $button) {
                $buttons[] = new ButtonDto($button[0]['callback_data'], $button[0]['text']);
            }
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $buttons;
    }
}
