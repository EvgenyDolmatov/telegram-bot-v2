<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelPollsChoiceSender extends AbstractSender
{
    private const string POLL_PREFIX = 'poll_';

    public function send(): void
    {
        $this->addToTrash();

        $text = StateEnum::CHANNEL_POLLS_CHOICE->title();
        $buttons = $this->getButtons();

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    private function getButtons(): array
    {
        $polls = $this->user->polls()->latest()->take(5)->get();

        $buttons = array_map(fn($poll) => new ButtonDto(
            callbackData: self::POLL_PREFIX . $poll['tg_message_id'],
            text: "✅ {$poll['question']}"
        ), $polls->toArray());

        $buttons[] = new ButtonDto(PollEnum::ACCEPT_POLLS->value, PollEnum::ACCEPT_POLLS->buttonText());

        return $buttons;
    }
}
