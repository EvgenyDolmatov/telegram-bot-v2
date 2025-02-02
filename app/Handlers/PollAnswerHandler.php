<?php

namespace App\Handlers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Enums\StateEnum;
use App\Models\Game;
use App\Models\GamePoll;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class PollAnswerHandler
{
    protected User $user;
    private SenderService $senderService;
    protected MessageSender $messageBuilder;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
        $this->messageBuilder = (new MessageSender())->setBuilder(new MessageBuilder());
        $this->senderService = new SenderService($repository, $telegramService);
    }

    public function handle(): void
    {
        $user = $this->user;
        $game = $user->games->last();
        $poll = $this->getPoll($game);

        $dto = $this->repository->createDto();
        $answer = isset($dto->getOptionIds()[0]) ? $dto->getOptionIds()[0] : null;

        $gamePoll = GamePoll::where('chat_id', $user->tg_chat_id)
            ->where('game_id', $game->id)
            ->where('poll_id', $poll->id)
            ->first();

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $gamePoll->started_at);
        $currentTime = Carbon::now();
        $diffInSeconds = $date->timestamp - $currentTime->timestamp;

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'answer' => $answer,
            'time' => $diffInSeconds,
            'points' => 123 // TODO: Calculate and save points
        ]);

        $pollIds = explode(',', $game->poll_ids);
        $pollsCount = count($pollIds);
        $gameAnswersCount = $game->results->count();

        if ($pollsCount > $gameAnswersCount) {
            $this->telegramService->resetQueue();
            StateEnum::GameplayQuizProcess
                ->sender($this->repository, $this->telegramService, $this->user)
                ->send();
            return;
        }

        $this->sendMessage(
            text: "Игра завершена",
            chatId: $this->user->tg_chat_id
        );
    }

    private function sendMessage(
        string $text,
        ?array $buttons = null,
        bool   $isTrash = true,
        ?int   $chatId = null
    ): Response {
        $message = $this->messageBuilder->createMessage($text, $buttons);
        return $this->senderService->sendMessage($message, $isTrash, $chatId);
    }

    private function getPoll(Game $game): Poll
    {
        $gameResults = $game->results()->where('user_id', $this->user->id)->get();
        $pollIds = explode(',', $game->poll_ids);

        $resultsCount = count($gameResults);

        return Poll::where('tg_message_id', $pollIds[$resultsCount])->first();
    }
}
