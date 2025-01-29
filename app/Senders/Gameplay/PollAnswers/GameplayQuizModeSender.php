<?php

namespace App\Senders\Gameplay\PollAnswers;

use App\Enums\GameplayEnum;
use App\Models\Poll;
use App\Senders\AbstractSender;
use Exception;

class GameplayQuizModeSender extends AbstractSender
{
    private const GameplayEnum STATE = GameplayEnum::QuizMode;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $this->sendFirstPoll();
    }

    private function sendFirstPoll(): void
    {
        $game = $this->user->games->last(); // TODO: Change logic for this
        $pollIds = explode(',', $game->poll_ids);
        $gamePoll = Poll::whereIn('tg_message_id', $pollIds)->get()->first();

        $this->sendPoll(
            $gamePoll->question,
            array_map(fn ($option) => $option['text'], $gamePoll->options->toArray()),
            true,
            $gamePoll->correct_option_id
        );
    }
}
