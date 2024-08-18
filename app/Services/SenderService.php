<?php

namespace App\Services;

use App\Builder\Message\Message;
use App\Builder\Poll\Poll;
use App\Constants\CommonConstants;
use App\Models\TrashMessage;
use App\Repositories\RequestRepository;
use App\Repositories\ResponseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

readonly class SenderService
{
    public function __construct(
        private Request         $request,
        private TelegramService $telegramService
    ) {
    }

    /**
     * Send photo
     *
     * @param Message $message
     * @param string $imageUrl
     * @param bool $isTrash
     * @return void
     */
    public function sendPhoto(Message $message, string $imageUrl, bool $isTrash = true): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendPhoto';
        $chat = (new RequestRepository($this->request))->convertToChat();
        $buttons = $message->getButtons();

        $body = [
            'chat_id' => $chat->getId(),
            'parse_mode' => 'html',
            'photo' => $imageUrl,
            'caption' => $message->getText()
        ];

        $body = $this->addButtonsToBody($buttons, $body);

        $response = Http::post($url, $body);
        $this->updateChatMessages(
            response: $response,
            isTrash: $isTrash
        );

        Log::debug('BOT: ' . $response);
    }

    /**
     * Send simple message or message with buttons
     *
     * @param Message $message
     * @param bool $isTrash
     * @return void
     */
    public function sendMessage(Message $message, bool $isTrash = true): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendMessage';
        $chat = (new RequestRepository($this->request))->convertToChat();
        $buttons = $message->getButtons();

        $body = [
            'chat_id' => $chat->getId(),
            'parse_mode' => 'html',
            'text' => $message->getText()
        ];

        $body = $this->addButtonsToBody($buttons, $body);

        $response = Http::post($url, $body);
        $this->updateChatMessages(
            response: $response,
            isTrash: $isTrash
        );

        Log::debug('BOT: ' . $response);
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
     * @param bool $isTrash
     * @return void
     */
    public function sendPoll(Poll $poll, bool $isTrash = true): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendPoll';
        $chat = (new RequestRepository($this->request))->convertToChat();

        $body = [
            "chat_id" => $chat->getId(),
            "question" => $poll->getQuestion(),
            "options" => $poll->getOptions(),
            "type" => $poll->getIsQuiz() ? "quiz" : "regular",
            "is_anonymous" => $poll->getIsAnonymous(),
        ];

        if ($poll->getIsQuiz()) {
            $body["correct_option_id"] = $poll->getCorrectOptionId();
        }

        $response = Http::post($url, $body);
        $this->updateChatMessages(
            response: $response,
            isTrash: $isTrash
        );

        Log::debug('BOT: ' . $response);
    }

    /**
     * Remove old messages and prepare messages for removing for next step
     *
     * @param $response
     * @param bool $isTrash
     * @return void
     */
    public function updateChatMessages($response, bool $isTrash = true): void
    {
        if (json_decode($response, true)['ok']) {
            $responseRepository = new ResponseRepository($response);
            $chatDto = $responseRepository->convertToChat();
            $messageDto = $responseRepository->convertToMessage();

            $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/deleteMessages';
            $trashMessages = TrashMessage::where('chat_id', $chatDto->getId())->where('is_trash', true)->get();

            if ($trashMessages->count()) {
                $trashMessageIds = [];

                foreach ($trashMessages as $trashMessage) {
                    $trashMessageIds[] = $trashMessage->message_id;
                    $trashMessage->delete();
                }

                $data = [
                    'chat_id' => $chatDto->getId(),
                    'message_ids' => $trashMessageIds
                ];

                // Delete messages
                Http::post($url, $data);
            }

            // Prepare trash messages for next step
            TrashMessage::add($chatDto, $messageDto, $isTrash);
        }
    }

    /**
     * Check if user is chat member
     *
     * @return bool
     */
    public function isMembership(): bool
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/getChatMember';
        $user = (new RequestRepository($this->request))->convertToUser();

        $body = [
            "chat_id" => -1001540575721,
            "user_id" => $user->getId()
        ];

        $allowedUserStatuses = ['administrator', 'creator', 'member'];
        $data = json_decode(Http::post($url, $body), true);

        return isset($data['result']['status']) && (in_array($data['result']['status'], $allowedUserStatuses));
    }
}
