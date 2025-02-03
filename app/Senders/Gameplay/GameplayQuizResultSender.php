<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Handlers\PollAnswerHandler;
use App\Models\Poll;
use App\Repositories\Telegram\Message\MessageTextRepository;
use App\Senders\AbstractSender;
use App\States\UserContext;
use Exception;

class GameplayQuizResultSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayCountdownShow;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $this->addToTrash();

        $this->sendCountdownMessage();
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

        sleep(1);
        $this->updateState(StateEnum::GameplayQuizProcess);

        $nextState = StateEnum::GameplayQuizProcess;
        $nextState->sender($this->repository, $this->telegramService, $this->user)->send();
    }
}
