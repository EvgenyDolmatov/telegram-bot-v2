<?php

namespace App\Services;

use App\Builder\Poll\Poll;
use App\Constants\CommonConstants;
use App\Repositories\RequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SendPollService
{
    public function __construct(
        private Request         $request,
        private TelegramService $telegramService,
        private Poll            $poll
    ) {
    }

    public function send(): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendPoll';
        $chat = (new RequestRepository($this->request))->convertToChat();

        $body = [
            "chat_id" => $chat->getId(),
            "question" => $this->poll->getQuestion(),
            "options" => $this->poll->getOptions(),
            "type" => $this->poll->getIsQuiz() ? "quiz" : "regular",
            "is_anonymous" => $this->poll->getIsAnonymous(),
        ];

        if ($this->poll->getIsQuiz()) {
            $body["correct_option_id"] = $this->poll->getCorrectOptionId();
        }

        Http::post($url, $body);
    }
}
