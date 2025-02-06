<?php

namespace App\Jobs;

use App\Enums\StateEnum;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPollJob implements ShouldQueue
{
    use Queueable;

    private SenderInterface $sender;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly RepositoryInterface $repository,
        private readonly TelegramService $telegramService,
        private readonly User $user,
        private readonly GamePoll $gamePoll
    ) {
        $this->sender = StateEnum::GameplayQuizProcess->sender($this->repository, $this->telegramService, $this->user);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->user->state !== StateEnum::GameplayQuizProcess->value) {
            Log::debug('NOT STATE');
            return;
        }

        Log::debug('check result');

        $result = GamePollResult::where('user_id', $this->user->id)
            ->where('game_id', $this->gamePoll->game_id)
            ->where('poll_id', $this->gamePoll->poll_id)
            ->first();

        if ($result) {
            Log::debug('RESULT HAS!');
            $this->sender->send();

            sleep(1);
            return;
        }

        Log::debug('bbb');

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $this->gamePoll->game_id,
            'poll_id' => $this->gamePoll->poll_id,
            'answer' => null,
            'time' => 5,
            'points' => 0,
        ]);

        Log::debug('ccc');

        sleep(5);
        $this->sender->send();
    }
}
