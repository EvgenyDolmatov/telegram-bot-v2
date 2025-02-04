<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\Gameplay\GameplayQuizProcessSender;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPollJob implements ShouldQueue
{
    use Queueable;

    private GameplayQuizProcessSender $sender;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly RepositoryInterface $repository,
        private readonly TelegramService $telegramService,
        private readonly User $user,
        private readonly Game $game,
        private readonly Poll $poll,
    ) {
        $this->sender = new GameplayQuizProcessSender($this->repository, $this->telegramService, $this->user);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('aaa');

        $this->sender->sendGamePoll($this->game, $this->poll);

        GamePoll::create([
            'game_id' => $this->game->id,
            'poll_id' => $this->poll->id,
            'chat_id' => $this->user->tg_chat_id
        ]);

        sleep(5);

        $response = GamePollResult::where('user_id', $this->user->id)
            ->where('game_id', $this->game->id)
            ->where('poll_id', $this->poll->id)
            ->first();

        if (!$response) {
            GamePollResult::create([
                'user_id' => $this->user->id,
                'game_id' => $this->game->id,
                'poll_id' => $this->poll->id,
                'answer' => null,
                'time' => 5,
                'points' => 0,
            ]);
        }

        if ($nextPoll = $this->sender->getNextGamePoll($this->game)) {
            SendPollJob::dispatch($this->repository, $this->telegramService, $this->user, $this->game, $nextPoll);
        } else {
            $this->sender->sendResults();
        }

        SendPollJob::dispatch($this->repository, $this->telegramService, $this->user, $this->game, $nextPoll);
    }
}
