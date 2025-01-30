<?php

namespace App\Handlers;

use App\Models\GamePollResult;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class PollAnswerHandler
{
    protected User $user;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
    }

    public function handle(): void
    {
        $game = $this->user->games->last();

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

        Log::debug("PollAnswerHandler");

        // send next message ...
    }
}
