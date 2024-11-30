<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelPollsSentSuccessSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();


        // TODO: write code


        $text = StateEnum::CHANNEL_POLLS_SENT_SUCCESS->title();
        $buttons = [new ButtonDto("/" . CommandEnum::START->value, "Вернуться в начало")];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    private function normalizeChannelName(string $channelName): string
    {
        if (str_contains($channelName, 'https://t.me/')) {
            return "@" . substr($channelName, 13);
        }

        return '@' . ltrim($channelName, '@');
    }

    private function sendToChannel(string $channelName): void
    {
        // TODO: write
    }
}
