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
     * Send simple message or message with buttons
     *
     * @param Message $message
     * @return void
     */
    public function sendMessage(Message $message): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendMessage';
        $chat = (new RequestRepository($this->request))->convertToChat();
        $buttons = $message->getButtons();

        $body = [
            'chat_id' => $chat->getId(),
            'parse_mode' => 'html',
            'text' => $message->getText()
        ];

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

        $response = Http::post($url, $body);
        $this->updateChatMessages($response);

        Log::debug('BOT: ' . $response);
    }

    /**
     * Send poll or quiz
     *
     * @param Poll $poll
     * @return void
     */
    public function sendPoll(Poll $poll): void
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
        $this->updateChatMessages($response);

        Log::debug('BOT: ' . $response);
    }

    /**
     * Remove old messages and prepare messages for removing for next step
     *
     * @param $response
     * @return void
     */
    public function updateChatMessages($response): void
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
            TrashMessage::add($chatDto, $messageDto, true);
        }
    }
}
