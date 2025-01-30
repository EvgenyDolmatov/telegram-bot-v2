<?php

namespace App\Handlers;

use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Models\Game;
use App\Models\GamePollResult;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use Exception;
use Illuminate\Http\Client\Response;

class PollAnswerHandler
{
    protected User $user;
    private SenderService $sender;

    public function __construct(
        protected readonly TelegramService $telegramService,
        protected readonly RepositoryInterface $repository
    ) {
        $this->user = User::getOrCreate($repository);
        $this->sender = new SenderService($repository, $telegramService);
    }

    public function handle(): void
    {
        $game = $this->user->games->last();
        $poll = $this->getPoll($game);

        GamePollResult::create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'poll_id' => $poll->id,
            'answer' => 'a', // TODO: Save real answer
            'time' => 4, // TODO: Save real time spent
            'points' => 123 // TODO: Calculate and save points
        ]);

        // send next message ...
        $game = $this->user->games->last(); // TODO: Change logic for this
        $pollIds = explode(',', $game->poll_ids);
        $gamePoll = Poll::whereIn('tg_message_id', $pollIds)->get()->first();

        $this->sendPoll(
            $gamePoll->question,
            array_map(fn ($option) => $option['text'], $gamePoll->options->toArray()),
            true,
            $gamePoll->correct_option_id,
            $this->user->tg_chat_id
        );

        // TODO: Check if user gave all answers. If yes, send message with results...
    }

    private function sendPoll(
        string  $question,
        array   $options,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        ?int    $chatId = null,
        bool    $isTrash = true
    ): Response {
        try {
            $pollBuilder = (new PollSender())
                ->setBuilder(new PollBuilder())
                ->createPoll($question, $options, $isQuiz, $correctOptionId);

            $response = $this->sender->sendPoll($pollBuilder, $chatId, $isTrash);
        } catch (\Throwable $exception) {
            throw new Exception('An error occurred while submitting the poll');
        }

        return $response;
    }

    private function getPoll(Game $game): Poll
    {
        $gameResults = $game->results()->where('user_id', $this->user->id)->get();
        $pollIds = explode(',', $game->poll_ids);

        $pollIdsCount = count($pollIds);
        $resultsCount = count($gameResults);

        if ($pollIdsCount > $resultsCount) {
            $pollIndex = $pollIds[$resultsCount];
        }

        return Poll::where('tg_message_id', $pollIndex)->first();
    }
}
