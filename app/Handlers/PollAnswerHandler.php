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

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'answer' => $answer,
            'time' => 1,
            'points' => 123 // TODO: Calculate and save points
        ]);
    }
}
