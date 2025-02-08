<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Senders\AbstractSender;
use Exception;
use Illuminate\Support\Collection;

class GameplayQuizProcessSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayQuizProcess;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $user = $this->user;
        $game = $user->games->last();
        $timeLimit = $game->time_limit;

        if (!$poll = $this->getNextGamePoll($game)) {
            $this->sendMessage('END...');
            return;
        }

        $result = null;
        $this->sendGamePoll($game, $poll, $timeLimit);

        for ($i = 0; $i < $timeLimit; $i++) {
            sleep(1);

            $result = GamePollResult::where('user_id', $this->user->id)
                ->where('game_id', $game->id)
                ->where('poll_id', $poll->id)
                ->first();

            if ($result) {
                self::STATE->sender($this->repository, $this->telegramService, $user)->send();
                return;
            }
        }

        if (!$result) {
            GamePollResult::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'poll_id' => $poll->id,
                'answer' => null,
                'time' => $timeLimit,
                'points' => 0
            ]);
        }

        self::STATE->sender($this->repository, $this->telegramService, $user)->send();
    }

    public function getNextGamePoll(Game $game): ?Poll
    {
        $leftGamePolls = $this->getLeftGamePolls($game);

        return $leftGamePolls->first();
    }

    private function getLeftGamePolls(Game $game): ?Collection
    {
        $allTgMessageIds = explode(',', $game->poll_ids);
        $notActualPollIds = GamePoll::where('game_id', $game->id)
            ->where('chat_id', $this->user->tg_chat_id)
            ->pluck('poll_id')
            ->toArray();
        $notActualTgMessageIds = Poll::whereIn('id', $notActualPollIds)->pluck('tg_message_id')->toArray();

        $result = array_diff($allTgMessageIds, $notActualTgMessageIds);

        return Poll::whereIn('tg_message_id', $result)->get();
    }

    public function sendGamePoll(Game $game, Poll $poll, int $timeLimit = 5): GamePoll
    {
        $this->sendPoll(
            question: $poll->question,
            options: array_map(fn ($option) => $option['text'], $poll->options->toArray()),
            isQuiz: true,
            correctOptionId: $poll->correct_option_id,
            timeLimit: $timeLimit,
            chatId: $this->user->tg_chat_id,
            isTrash: false,
        );

        return GamePoll::create([
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'chat_id' => $this->user->tg_chat_id
        ]);
    }
}
