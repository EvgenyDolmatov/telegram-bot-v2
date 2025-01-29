<?php

namespace App\States\Gameplay\PollAnswers;

use App\Enums\GameplayEnum;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameplayQuizModeState extends AbstractState implements UserState
{
    private const GameplayEnum STATE = GameplayEnum::QuizMode;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = self::STATE;

        // Save poll answer to DB
        $game = $this->user->games->last();


        // Get poll id
        $pollResults = $game->results()->where('user_id', $this->user->id)->get();
        $pollIds = explode(',', $game->poll_ids);

        if (!$pollResults->count()) {
            $pollId = $pollIds[0];
        } else {
            $pollId = isset($pollIds[$pollResults]) ?? $pollIds[0]; // TODO: Attention!!! This is test!
        }



        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => Poll::where('tg_message_id', $pollId)->first()->id,
            'answer' => 'a',
            'time' => 4,
            'points' => 123
        ]);



        // Update user step
//        $this->updateState($state, $context);

        // Send next question
        $sender = $state->sender($this->repository, $this->telegramService, $this->user);
        $sender->send();
    }
}
