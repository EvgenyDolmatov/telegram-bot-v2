<?php

namespace App\Senders\Game;

use App\Dto\ButtonDto;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GamePollsChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_POLLS_CHOICE;
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

        $buttons[] = new ButtonDto(CallbackEnum::GAME_POLLS_SAVE->value, CallbackEnum::GAME_POLLS_SAVE->buttonText());

        return $buttons;
    }

    private function process(string $text, array $buttons): void
    {
        if (!$preparedPoll = $this->user->preparedPolls()->first()) {
            throw new \Exception('Prepared poll not found');
        }

        // Edit message
        if (str_starts_with($this->getInputText(), self::POLL_PREFIX)) {
            $this->editMessage(
                messageId: $preparedPoll->tg_message_id,
                text: $text,
                buttons: $buttons
            );
            return;
        }

        // Send message
        $response = $this->sendMessage($text, $buttons);
        $data = json_decode($response, true);

        // TODO: Check if this need?..
        $preparedPoll->update(['tg_message_id' => $data['result']['message_id']] ?? null);
    }
}
