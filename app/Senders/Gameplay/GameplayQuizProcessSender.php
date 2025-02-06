<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Jobs\SendPollJob;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Senders\AbstractSender;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class GameplayQuizProcessSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayQuizProcess;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        Log::debug('Quiz process');
        $user = $this->user;
        $game = $user->games->last();
        $poll = $this->getNextGamePoll($game);

        $result = null;

        if ($poll) {
            Log::debug("POLL: " . $poll->question);
            Log::debug('Poll process');
            $gamePoll = $this->sendGamePoll($game, $poll);

            for ($i = 0; $i < 5; $i++) {
                $result = GamePollResult::where('user_id', $this->user->id)
                    ->where('game_id', $gamePoll->game_id)
                    ->where('poll_id', $gamePoll->poll_id)
                    ->first();

                Log::debug('Wait...');
                sleep(1);
            }
        }

        if ($result) {
            $nextPoll = $this->getNextGamePoll($game);
            Log::debug('NEXT');
            Log::debug("POLL: " . $nextPoll->question);

            return;
        }

        $this->sendMessage('END...');
    }

    public function getNextGamePoll(Game $game): ?Poll
    {
        $leftGamePolls = $this->getLeftGamePolls($game);

        Log::debug("Left game polls: " . count($leftGamePolls));
        Log::debug("Left game poll: " . $leftGamePolls->first()?->question);

        return $this->getLeftGamePolls($game)->first() ?? null;
    }

    private function getLeftGamePolls(Game $game)
    {
        $allIds = Poll::whereIn('tg_message_id', explode(',', $game->poll_ids))->pluck('id')->toArray();
        $notActualIds = GamePoll::where('game_id', $game->id)->pluck('poll_id')->toArray() ?? [];
        $res = array_diff($allIds, $notActualIds);

        $polls = Poll::whereIn('tg_message_id', $res)->get();


        Log::debug("All Ids: " . implode(',', $allIds));
        Log::debug("Not Actual Ids: " . implode(',', $notActualIds));

        return $polls;
    }

    public function sendGamePoll(Game $game, Poll $poll): GamePoll
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

        return GamePoll::create([
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'chat_id' => $this->user->tg_chat_id
        ]);
    }
}
