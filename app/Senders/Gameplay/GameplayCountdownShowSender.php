<?php

namespace App\Senders\Gameplay;

use App\Enums\GameplayEnum;
use App\Enums\StateEnum;
use App\Models\Poll;
use App\Repositories\Telegram\Message\MessageTextRepository;
use App\Senders\AbstractSender;
use Exception;
use Illuminate\Support\Facades\Log;

class GameplayCountdownShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayCountdownShow;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $this->addToTrash();

        $this->sendCountdownMessage();

        $quizModeState = GameplayEnum::QuizMode;

        $this->updateState($quizModeState);

        $sender = $quizModeState->sender($this->repository, $this->telegramService, $this->user);
        $sender->send();
    }

    /**
     * @throws Exception
     */
    private function sendCountdownMessage(): void
    {
        $response = $this->sendMessage("3️⃣ ...");
        $data = json_decode($response, true);

        if (!array_key_exists('result', $data)) {
            throw new Exception("Message data is unavailable.");
        }

        $messageDto = (new MessageTextRepository($data['result']))->createDto();
        $messageId = $messageDto->getId();

        sleep(1);
        $this->editMessage($messageId, '2⃣ ...');

        sleep(1);
        $this->editMessage($messageId, '1⃣ ...');

        sleep(1);
        $this->editMessage($messageId, '🚀 Приготовьтесь!');
    }
}
