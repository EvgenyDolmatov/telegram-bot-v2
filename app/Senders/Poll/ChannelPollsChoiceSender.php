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

        $preparedPoll = $this->user->preparedPolls()->first();
        if (!$preparedPoll) {
            throw new \Exception('Prepared poll not found');
        }

        if (str_starts_with($this->getInputText(), self::POLL_PREFIX)) {
            $message = $this->messageBuilder->createMessage($text, $buttons);
            $this->senderService->editMessage($message, $preparedPoll->tg_message_id);
        } else {
            $response = $this->sendMessage($text, $buttons);
            $data = json_decode($response, true);

            $preparedPoll->update(['tg_message_id' => $data['result']['message_id']] ?? null);
        }
    }

    /**
     * @throws \Exception
     */
    private function getButtons(): array
    {
        $preparedPoll = $this->user->preparedPolls()->first();
        if (!$preparedPoll) {
            throw new \Exception('Prepared poll not found');
        }

        $allPollIds = explode(',', $preparedPoll->poll_ids);
        $checkedPollIds = explode(',', $preparedPoll->checked_poll_ids);

        $input = $this->getInputText();
        if (str_starts_with($input, self::POLL_PREFIX)) {
            $pollId = mb_substr($input, strlen(self::POLL_PREFIX));

            if (!in_array($pollId, $checkedPollIds)) {
                $checkedPollIds[] = $pollId;
            } else {
                $checkedPollIds = array_diff($checkedPollIds, [$pollId]);
            }

            $preparedPoll->update(['checked_poll_ids' => implode(',', $checkedPollIds)]);
        }

        $polls = $this->user->polls() ->whereIn('tg_message_id', $allPollIds)->get();

        $buttons = [];
        foreach ($polls as $poll) {
            $symbol = in_array($poll->tg_message_id, $checkedPollIds) ? "✅ " : "❌ " ;
            $buttons[] = new ButtonDto(
                callbackData: self::POLL_PREFIX . $poll->tg_message_id,
                text: $symbol . $poll['question']);
        }

        $buttons[] = new ButtonDto(PollEnum::ACCEPT_POLLS->value, PollEnum::ACCEPT_POLLS->buttonText());

        return $buttons;
    }
}
