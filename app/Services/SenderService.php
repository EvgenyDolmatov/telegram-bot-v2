<?php

namespace App\Services;

use App\Builder\Message\Message;
use App\Builder\Poll\Poll;
use App\Dto\Telegram\MessageDto;
use App\Exceptions\ChatNotFoundException;
use App\Exceptions\ResponseException;
use App\Models\TrashMessage;
use App\Repositories\Telegram\Request\RepositoryInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class SenderService
{
    public function __construct(
        private RepositoryInterface $repository,
        private TelegramService     $telegramService
    ) {
    }

    /**
     * Send photo
     *
     * @param Message $message
     * @param string $imageUrl
     * @param bool $isTrash
     * @param int|null $chatId
     * @return Response
     * @throws ResponseException
     */
    public function sendPhoto(
        Message $message,
        string $imageUrl,
        bool $isTrash = true,
        int $chatId = null
    ): Response {
        $url = TelegramService::BASE_URL . $this->telegramService->token . '/sendPhoto';
        $chatId = $this->getChatId($chatId);
        $buttons = $message->getButtons();

        $body = [
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            'photo' => $imageUrl,
            'caption' => $message->getText()
        ];

        $body = $this->addButtonsToBody($buttons, $body);

        $response = Http::post($url, $body);
        $this->updateChatMessages($isTrash);

        Log::debug('BOT: ' . $response);
        return $response;
    }

    /**
     * Send simple message or message with buttons
     *
     * @param Message $message
     * @param bool $isTrash
     * @param int|null $chatId
     * @return Response
     * @throws ResponseException
     */
    public function sendMessage(
        Message $message,
        bool $isTrash = true,
        ?int $chatId = null
    ): Response {
        $url = TelegramService::BASE_URL . $this->telegramService->token . '/sendMessage';
        $chatId = $this->getChatId($chatId);
        $buttons = $message->getButtons();

        $body = [
            'chat_id' => $chatId,
            'parse_mode' => 'html',
            'text' => $message->getText()
        ];

        $body = $this->addButtonsToBody($buttons, $body);

        $response = Http::post($url, $body);
        $this->updateChatMessages($isTrash);

        Log::debug('BOT: ' . $response);

        return $response;
    }

    /**
     * Update message by message id
     */
    public function editMessage(Message $message, int $messageId, ?int $chatId = null): Response
    {
        $url = $this->getUrl('editMessageText');

        if (!$chatId) {
            $chat = $this->getMessageDto()->getChat();
            $chatId = $chat->getId();
        }

        $body = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'parse_mode' => 'html',
            'text' => $message->getText()
        ];

        $body = $this->addButtonsToBody($message->getButtons(), $body);
        $response = Http::post($url, $body);

        Log::debug('BOT: ' . $response);

        return $response;
    }

    /**
     * Add buttons to body if exists
     *
     * @param array $buttons
     * @param array $body
     * @return array
     */
    public function addButtonsToBody(array $buttons, array $body): array
    {
        if (count($buttons) !== 0) {
            foreach ($buttons as $button) {
                $body['reply_markup']['inline_keyboard'][] = [
                    [
                        'text' => $button->getText(),
                        'callback_data' => $button->getCallbackData()
                    ]
                ];
            }
        }

        return $body;
    }

    /**
     * Send poll or quiz
     *
     * @param Poll $poll
     * @param int|null $chatId
     * @param bool $isTrash
     * @return Response
     * @throws \Exception
     */
    public function sendPoll(Poll $poll, ?int $chatId = null, bool $isTrash = true): Response
    {
        $url = $this->getUrl('sendPoll');

        $body = [
            "chat_id" => $chatId ?? $this->getChatId($chatId),
            "question" => $poll->getQuestion(),
            "options" => $poll->getOptions(),
            "type" => $poll->getIsQuiz() ? "quiz" : "regular",
            "is_anonymous" => $poll->getIsAnonymous(),
        ];

        if ($poll->getIsQuiz()) {
            $body["correct_option_id"] = $poll->getCorrectOptionId();
        }

        $response = Http::post($url, $body);
        $this->updateChatMessages($isTrash);

        Log::debug('BOT: ' . $response);

        return $response;
    }

    /**
     * Remove old messages and prepare messages for removing for next step
     *
     * @param bool $isTrash
     * @return void
     * @throws ResponseException
     */
    public function updateChatMessages(bool $isTrash = true): void
    {
        $url = TelegramService::BASE_URL . $this->telegramService->token . '/deleteMessages';
        $messageDto = $this->getMessageDto();
        $chatId = $messageDto->getChat()->getId();
        $trashMessages = TrashMessage::where('chat_id', $chatId)->where('is_trash', true)->get();

        if ($trashMessages->count()) {
            $trashMessageIds = [];

            foreach ($trashMessages as $trashMessage) {
                $trashMessageIds[] = $trashMessage->message_id;
                $trashMessage->delete();
            }

            $data = [
                'chat_id' => $chatId,
                'message_ids' => $trashMessageIds
            ];

            // Delete messages
            Http::post($url, $data);
        }

        // Prepare trash messages for next step
        TrashMessage::add($chatId, $messageDto->getId(), $isTrash);
    }

    /**
     * Check if user is chat member
     *
     * @return bool
     * @throws \Exception
     */
    public function isMembership(): bool
    {
        $url = TelegramService::BASE_URL . $this->telegramService->token . '/getChatMember';
        $user = $this->getMessageDto()->getFrom();

        $body = [
            "chat_id" => config('services.telegram.chatId'),
            "user_id" => $user->getId()
        ];

        $allowedUserStatuses = ['administrator', 'creator', 'member'];
        $data = json_decode(Http::post($url, $body), true);

        return isset($data['result']['status']) && (in_array($data['result']['status'], $allowedUserStatuses));
    }

    /**
     * Upload photo from telegram by file ID
     *
     * @param string $fileId
     * @return string|null
     */
    public function uploadPhoto(string $fileId): ?string
    {
        $url = $this->getUrl("getFile?file_id=$fileId");
        $response = Http::get($url);
        $path = null;

        if ($response->successful()) {
            $fileInfo = json_decode($response, true);
            $filePath = $fileInfo['result']['file_path'];
            $photoUrl = TelegramService::ROOT_URL . '/file/bot' . $this->telegramService->token . '/' . $filePath;

            $path = 'newsletters/' . time() . '.jpg';
            $file = Http::get($photoUrl)->body();

            Storage::disk('uploads')->put($path, $file);
        }

        return $path;
    }

    /**
     * @throws ChatNotFoundException
     */
    public function getChatByChannelName(string $channelName): Response
    {
        try {
            return Http::get($this->getUrl("getChat?chat_id={$channelName}"));
        } catch (Throwable $e) {
            if ($e->getCode() === 400) {
                throw new ChatNotFoundException("Chat $channelName not found");
            }

            throw new ChatNotFoundException(
                message: "Error of getting chat info by chat name $channelName",
                code: $e->getCode(),
                previous: $e
            );
        }
    }

    private function getUrl(string $path): string
    {
        return TelegramService::BASE_URL . $this->telegramService->token . '/' . ltrim($path, '/');
    }

    private function getMessageDto(): MessageDto
    {
        $dto = $this->repository->createDto();

        if (method_exists($dto, 'getMessage')) {
            return $dto->getMessage();
        }

        return $dto;
    }

    private function getChatId(?int $chatId = null): int
    {
        return $chatId ?? $this->getMessageDto()->getChat()->getId();
    }
}
