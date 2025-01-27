<?php

namespace App\Senders\Gameplay;

use App\Enums\State\GameplayEnum;
use App\Models\Poll;
use App\Repositories\Telegram\Message\MessageTextRepository;
use App\Senders\AbstractSender;
use Exception;
use Illuminate\Support\Facades\Log;

class GameplayCountdownShowSender extends AbstractSender
{
    private const GameplayEnum STATE = GameplayEnum::CountdownShow;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $this->addToTrash();

        $this->sendCountdownMessage();
        $this->sendFirstPoll();
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

    private function sendFirstPoll(): void
    {
        $game = $this->user->games->last(); // TODO: Change logic for this
        $pollIds = explode(',', $game->poll_ids);
        $gamePolls = Poll::whereIn('tg_message_id', $pollIds)->get();

        Log::debug($gamePolls->count());

        foreach ($gamePolls as $poll) {
            $this->sendPoll(
                $poll->question,
                array_map(fn ($option) => $option['text'], $poll->options->toArray()),
                true,
                $poll->correct_option_id
            );
        }
    }
}
