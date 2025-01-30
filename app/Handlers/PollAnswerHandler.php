<?php

namespace App\Handlers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
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

        $pollIds = explode(',', $game->poll_ids);
        $pollsCount = count($pollIds);
        $gameAnswersCount = $game->results->count();

        if ($pollsCount > $gameAnswersCount) {
            $this->sendPoll(
                $poll->question,
                array_map(fn ($option) => $option['text'], $poll->options->toArray()),
                true,
                $poll->correct_option_id,
                $this->user->tg_chat_id
            );
            return;
        }

        $this->sendMessage(
            text: "Игра завершена",
            chatId: $this->user->tg_chat_id
        );
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

            $response = $this->senderService->sendPoll($pollBuilder, $chatId, $isTrash);
        } catch (\Throwable $exception) {
            throw new Exception('An error occurred while submitting the poll');
        }

        return $response;
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
