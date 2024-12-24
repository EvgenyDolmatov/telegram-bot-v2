<?php

namespace App\Senders\Game;

use App\Builder\Poll\PollBuilder;
use App\Dto\Telegram\ChannelDto;
use App\Enums\StateEnum;
use App\Exceptions\ChatNotFoundException;
use App\Models\Game;
use App\Models\Poll;
use App\Repositories\Telegram\Response\CommunityRepository;
use App\Senders\AbstractSender;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class GameSentToChannelSuccessSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_SENT_TO_CHANNEL_SUCCESS;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());

        // Send message for prepare
        $this->sendBeforeGameMessage();

        // In 10 seconds send first poll
        $this->sendPoll();
    }

    private function sendBeforeGameMessage(): void
    {
        $chatId = $this->getChannelDto()->getId();
        $game = $this->getGame();

        $text = "Начало новой игры...\n\n";
        $text .= "Название: {$game->title}\n";
        $text .= "Описание: {$game->description}\n";
        $text .= "Время на ответ: {$game->time_limit} секунд\n\n";
        $text .= "До начала игры осталось: 1 минута.";

        $this->sendMessage($text, null, true, $chatId);
    }

    private function sendPoll(): void
    {
        $optionLetters = ['a','b','c','d'];

        $chatId = $this->getChannelDto()->getId();
        $game = $this->getGame();
        $pollIds = explode(',', $game->poll_ids);

        $poll = Poll::where('tg_message_id', $pollIds[0])->first();

        if ($poll) {
            $options = array_map(fn ($option) => $option['text'], $poll->options->toArray());

            $correctOptionId = $poll->correct_option_id
                ? $optionLetters[$poll->correct_option_id]
                : null;

            $pollBuilder = $this->pollBuilder
                ->setBuilder(new PollBuilder())
                ->createPoll(
                    question: $poll->question,
                    options: $options,
                    isAnonymous: true,
                    isQuiz: !$poll->allows_multiple_answers,
                    correctOptionId: $correctOptionId,
                );

            $this->senderService->sendPoll($pollBuilder, $chatId);
        }
    }

    /**
     * @throws ChatNotFoundException
     */
    private function getChannelDto(): ChannelDto
    {
        $channelName = $this->getGame()->channel;

        if (!$channelName) {
            throw new ChatNotFoundException("Channel does not exist.");
        }

        try {
            $response = $this->senderService->getChatByChannelName($channelName);
            $channelDto = (new CommunityRepository(json_decode($response, true)))
                ->defineRepository()
                ->createDto();
        } catch (Throwable $e) {
            throw new ChatNotFoundException("Wrong channel $channelName");
        }

        return $channelDto;
    }

    /**
     * @throws Exception
     */
    private function getGame(): Game
    {
        try {
            $game = $this->user->games->last();
        } catch (Exception $e) {
            Log::error('Game does not exists for current user.', ['message' => $e->getMessage()]);
            throw new Exception('Game does not exists for current user.', 400);
        }

        return $game;
    }
}
