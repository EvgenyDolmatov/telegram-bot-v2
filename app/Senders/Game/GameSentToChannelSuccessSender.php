<?php

namespace App\Senders\Game;

use App\Dto\ChannelDto;
use App\Enums\StateEnum;
use App\Exceptions\ChatNotFoundException;
use App\Models\Game;
use App\Repositories\ChannelRepository;
use App\Repositories\MessageRepository;
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

        // Логика игры в канале
        $chatId = $this->getChannelDto()->getId();
        $game = $this->getGame();

        $timeLeft = 30;

        $text = "Начало новой игры...\n\n";
        $text .= "Название: {$game->title}\n";
        $text .= "Описание: {$game->description}\n";
        $text .= "Время на ответ: {$game->time_limit} секунд\n\n";
        $text .= "До начала игры осталось: {$timeLeft}";

        $response = $this->sendMessage($text, null, true, $chatId);
        $messageDto = (new MessageRepository($response))->getDto();

        Log::debug('RESPONSE: ' . $response);
        Log::debug('MessageID: ' . $messageDto->getId());
        Log::debug('MessageID: ' . $messageDto->getText());


        while ($timeLeft > 0) {
            sleep(1);
            $timeLeft--;
            $this->editMessage($messageDto->getId(), $text, null, $chatId);
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
            $channelDto = (new ChannelRepository($response))->getDto();
        } catch (Throwable $e) {
            throw new ChatNotFoundException("Wrong channel name $channelName");
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
