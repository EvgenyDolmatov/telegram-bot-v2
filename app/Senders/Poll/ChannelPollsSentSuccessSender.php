<?php

namespace App\Senders\Poll;

use App\Builder\Poll\PollBuilder;
use App\Dto\Telegram\ChannelDto;
use App\Enums\StateEnum;
use App\Exceptions\ChatNotFoundException;
use App\Models\Poll;
use App\Senders\AbstractSender;
use Throwable;

class ChannelPollsSentSuccessSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();
        $this->sendPollsToChannel();

        $this->sendMessage(
            text: StateEnum::CHANNEL_POLLS_SENT_SUCCESS->title(),
            buttons: StateEnum::CHANNEL_POLLS_SENT_SUCCESS->buttons()
        );
    }

    private function sendPollsToChannel(): void
    {
        $optionLetters = ['a','b','c','d'];
        $chatId = $this->getChannelDto()->getId();
        $preparedPoll = $this->user->preparedPolls()->first();

        if ($preparedPoll) {
            $pollIds = explode(',', $preparedPoll->poll_ids);

            foreach ($pollIds as $pollId) {
                if ($poll = Poll::where('tg_message_id', $pollId)->first()) {
                    $options = [];

                    foreach ($poll->options as $pollOption) {
                        $options[] = $pollOption->text;
                    }

                    $correctOptionId = $poll->correct_option_id ? $optionLetters[$poll->correct_option_id] : null;

                    // TODO: Сделать проверку и вывести сообщение пользователю о том, что нельзя добаавлять неанонимные опросы на канал
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

            $preparedPoll->delete();
        }
    }

    private function getChannelName(): string
    {
        $channelName = $this->getInputText();
        if (str_contains($channelName, 'https://t.me/')) {
            return "@" . substr($channelName, 13);
        }

        return '@' . ltrim($channelName, '@');
    }

    /**
     * @throws ChatNotFoundException
     */
    private function getChannelDto(): ChannelDto
    {
        $channelName = $this->getChannelName();

        try {
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
}
