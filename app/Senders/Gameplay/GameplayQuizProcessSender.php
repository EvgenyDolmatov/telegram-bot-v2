<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Handlers\PollAnswerHandler;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Repositories\Telegram\Message\MessageTextRepository;
use App\Repositories\Telegram\Response\PollAnswerRepository;
use App\Senders\AbstractSender;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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

        $pollsIds = explode(',', $game->poll_ids);
        $polls = $this->getActualPolls($game);

        $sentPolls = GamePoll::where('game_id', $game->id)->get();

        if (isset($pollsIds[count($sentPolls)])) {
            $nextPollId = $pollsIds[count($sentPolls)];
            $poll = Poll::where('tg_message_id', $nextPollId)->first();

            $gamePoll = $this->sendGamePoll($game, $poll);

            $gamePollResult = $this->checkForResponses($gamePoll);

            if (!$gamePollResult) {
                self::STATE->sender($this->repository, $this->telegramService, $user)->send();
            }
        } else {
            $this->sendMessage('Buy...', null, false, $user->tg_chat_id);
        }

//        $this->gameProcess();
    }


    private function getActualPolls(Game $game)
    {
        $pollsIds = explode(',', $game->poll_ids);

        return Poll::whereIn('tg_message_id', $pollsIds)->get();
    }



    private function checkForResponses(GamePoll $gamePoll): ?GamePollResult
    {
        $user = $this->user;
        $game = $user->games->last();

        $timeout = 5;
        $startTime = Carbon::parse('Y-m-d H:i:s', $gamePoll->started_at);

        $count = 0;
        while (Carbon::now()->timestamp - $startTime->timestamp <= $timeout) {
            $pollResult = GamePollResult::where('user_id', $user->id)
                ->where('game_id', $game->id)
                ->where('poll_id', $gamePoll->id)->first();

            if ($pollResult) {
                GamePollResult::create([
                    'user_id' => $user->id,
                    'game_id' => $game->id,
                    'poll_id' => $gamePoll->poll->id,
                    'answer' => 1,
                    'time' => $count,
                    'points' => 123 // TODO: Calculate and save points
                ]);

                return $pollResult;
            }

            $count++;
            sleep(1);
        }

        GamePollResult::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'poll_id' => $gamePoll->poll->id,
            'answer' => null,
            'time' => $count,
            'points' => 0
        ]);

        return null;
    }



    private function gameProcess(): void
    {
        $user = $this->user;
        $game = $user->games->last();

        $pollsIds  = explode(',', $game->poll_ids);
        $polls = Poll::whereIn('tg_message_id', $pollsIds)->get();

        $pollResults = GamePollResult::where('user_id', $user->id)->where('game_id', $game->id)->get();

        $poll = $polls->where('tg_message_id', $pollsIds[count($pollResults)])->first();

        $this->sendGamePoll($game, $poll);

        if ($this->repository instanceof PollAnswerRepository) {
            $handler = new PollAnswerHandler($this->telegramService, $this->repository);
            $handler->handle();
            return;
        }


        for ($i = 0; $i < 5; $i++) {
            $pollResult = GamePollResult::where('user_id', $this->user->id)
                ->where('game_id', $game->id)
                ->where('poll_id', $poll->id)->first();

            sleep(1);
        }

        if ($pollResult) {
            Log::debug('pollRes->HANDLER');
            $handler = new PollAnswerHandler($this->telegramService, $this->repository);
            $handler->handle();
            return;
        }

        if (!$pollResult) {
            GamePollResult::create([
                'user_id' => $this->user->id,
                'game_id' => $game->id,
                'poll_id' => $poll->id,
                'answer' => null,
                'time' => 4,
                'points' => 123
            ]);

            if (!isset($pollsIds[count($pollResults) + 1])) {
                $this->sendMessage('END...', null, false, $user->tg_chat_id);
                return;
            }

            $this->gameProcess();
        }
    }

    private function sendGamePoll(Game $game, Poll $poll): GamePoll
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

    private function getPollResult(Game $game, Poll $poll, int $timeout = 5): ?GamePollResult
    {
        $pollResult = null;

        for ($i = 0; $i < $timeout; $i++) {
            $result = GamePollResult::where('user_id', $this->user->id)
                ->where('game_id', $game->id)
                ->where('poll_id', $poll->id)->first();

            if ($result) {
                $pollResult = $result;
            }

            sleep(1);
        }

        return $pollResult;
    }
}
