<?php

namespace App\Senders\Game;

use App\Dto\ChannelDto;
use App\Enums\StateEnum;
use App\Exceptions\ChatNotFoundException;
use App\Models\Game;
use App\Senders\AbstractSender;

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

        $text = "Начало новой игры...\n\n";
        $text .= "Название: {$game->title}\n";
        $text .= "Описание: $game->description\n";
        $text .= "Время на ответ: {$game->time_limit} секунд";

        $this->sendMessage($text, null, true, $chatId);
    }

    /**
     * @throws ChatNotFoundException
     */
    private function getChannelDto(): ChannelDto
    {
        try {
            $channelName = $this->getGame()->channel;

            $response = $this->senderService->getChatByChannelName($channelName);
            $data = json_decode($response, true);
            $payload = $data['result'];

            return (new ChannelDto())
                ->setId($payload['id'])
                ->setTitle($payload['title'])
                ->setUsername($payload['username'])
                ->setType($payload['type'])
                ->setActiveUsernames($payload['active_usernames'])
                ->setInviteLink($payload['invite_link'])
                ->setIsHasVisibleHistory($payload['has_visible_history'])
                ->setIsCanSendPaidMedia($payload['can_send_paid_media'])
                ->setAvailableReactions($payload['available_reactions'])
                ->setMaxReactionCount($payload['max_reaction_count'])
                ->setAccentColorId($payload['accent_color_id']);
        } catch (Throwable $e) {
            throw new ChatNotFoundException("Wrong channel name $channelName");
        }
    }

    private function getGame(): Game
    {
        return $this->user->games->last();
    }
}
