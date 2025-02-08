<?php

namespace App\Handlers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PollAnswerHandler
{
    private const int POINTS = 1000;

    protected User $user;
    protected MessageSender $messageBuilder;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
        $this->messageBuilder = (new MessageSender())->setBuilder(new MessageBuilder());
    }

    public function handle(): void
    {
        $user = $this->user;
        $game = $user->games->last();
        $poll = $this->getCurrentGamePoll($game);

        $dto = $this->repository->createDto();
        $answer = isset($dto->getOptionIds()[0]) ? $dto->getOptionIds()[0] : null;
        $spentTime = $this->getSpentTime($game, $poll);

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'answer' => $answer,
            'time' => $spentTime,
            'points' => $this->calculatePoints($game, $spentTime)
        ]);
    }

    private function calculatePoints(Game $game, int $spentTime): int
    {
        $timeLimit = $game->time_limit;
        $pointsPerSecond = self::POINTS / $timeLimit;

        return round(self::POINTS - $pointsPerSecond * $spentTime);
    }

    private function getSpentTime(Game $game, Poll $poll): int
    {
        $gamePoll = GamePoll::where('game_id', $game->id)
            ->where('chat_id', $this->user->tg_chat_id)
            ->where('poll_id', $poll->id)
            ->first();

        $startTime = Carbon::parse($gamePoll->started_at);
        $now = Carbon::now();

        return floor($now->diffInSeconds($startTime));
    }

    public function getCurrentGamePoll(Game $game): Poll
    {
        return $this->getLeftGamePolls($game)->first();
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
}
