<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Senders\AbstractSender;
use Carbon\Carbon;
use Exception;

class GameplayQuizProcessSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayQuizProcess;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $game = $this->user->games->last();
        $this->sendPolls($game);
    }

    private function sendPolls(Game $game): void
    {
        $polls = $this->getLeftGamePolls($game);

        foreach ($polls as $poll) {
            $this->sendGamePoll($game, $poll);
            $this->waitForResponse($game, $poll);
        }

        $this->sendMessage('BUY!', null, false, $this->user->tg_chat_id);
    }

    private function getLeftGamePolls(Game $game)
    {
        $allIds = explode(',', $game->poll_ids);
        $notActualIds = GamePoll::where('game_id', $game->id)->pluck('poll_id');

        return Poll::whereIn('tg_message_id', $allIds)
            ->whereNotIn('tg_message_id', $notActualIds)
            ->get();
    }

    private function sendGamePoll(Game $game, Poll $poll): void
    {
        $this->sendPoll(
            question: $poll->question,
            options: array_map(fn ($option) => $option['text'], $poll->options->toArray()),
            isQuiz: true,
            correctOptionId: $poll->correct_option_id,
            timeLimit: 5,
            chatId: $this->user->tg_chat_id,
            isTrash: false,
        );

        GamePoll::create([
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'chat_id' => $this->user->tg_chat_id
        ]);
    }

    private function waitForResponse(Game $game, Poll $poll): void
    {
        $startTime = Carbon::now();

        while (Carbon::now()->diffInSeconds($startTime) < 5) {
            $response = GamePollResult::where('user_id', $this->user->id)
                ->where('game_id', $game->id)
                ->where('poll_id', $poll->id)
                ->first();

            if ($response) {
                return;
            }

            sleep(1);
        }

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'answer' => null,
            'time' => 5,
            'points' => 0
        ]);
    }
}
