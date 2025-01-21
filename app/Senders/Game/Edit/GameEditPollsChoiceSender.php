<?php

namespace App\Senders\Game\Edit;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameEditPollsChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GamePollsChoice;
    private const string POLL_PREFIX = 'poll_';

    public function send(): void
    {
        $this->addToTrash();

        $text = self::STATE->title();
        $buttons = $this->getButtons();

        $this->process($text, $buttons);
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

            // TODO: Continue here...
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

        return array_merge($buttons, self::STATE->buttons());
    }

    private function process(string $text, array $buttons): void
    {
        if (!$preparedPoll = $this->user->preparedPolls()->first()) {
            throw new \Exception('Prepared poll not found');
        }

        // Edit message
        if (str_starts_with($this->getInputText(), self::POLL_PREFIX)) {
            $this->editMessage($preparedPoll->tg_message_id, $text, $buttons);
            return;
        }

        // Send message
        $response = $this->sendMessage($text, $buttons);
        $data = json_decode($response, true);

        // TODO: Check if this need?..
        $preparedPoll->update(['tg_message_id' => $data['result']['message_id']] ?? null);
    }
}
