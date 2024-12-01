<?php

namespace App\Senders\Poll;

use App\Builder\Poll\PollBuilder;
use App\Dto\ButtonDto;
use App\Dto\ChannelDto;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Models\Poll;
use App\Repositories\RequestRepository;
use App\Senders\AbstractSender;

class ChannelPollsSentSuccessSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();
        $this->sendPollsToChannel();

        $text = StateEnum::CHANNEL_POLLS_SENT_SUCCESS->title();
        $buttons = [new ButtonDto(CommandEnum::START->getCommand(), "Вернуться в начало")];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    private function sendPollsToChannel(): void
    {
        $chatId = $this->getChannelDto()->getId();
        $preparedPolls = $this->user->preparedPolls()->first();

        if ($preparedPolls) {
            $pollIds = explode(',', $preparedPolls->poll_ids);

            foreach ($pollIds as $pollId) {
                if ($poll = Poll::where('tg_message_id', $pollId)->first()) {
                    $options = [];

                    foreach ($poll->options as $pollOption) {
                        $options[] = $pollOption->text;
                    }

                    // TODO: Check if poll is anonymous
                    $pollBuilder = $this->pollBuilder
                        ->setBuilder(new PollBuilder())
                        ->createPoll(
                            question: $poll->question,
                            options: $options,
                            isAnonymous: true,
                            isQuiz: !$poll->allows_multiple_answers,
                            correctOptionId: $poll->correct_option_id,
                        );

                    $this->senderService->sendPoll($pollBuilder, $chatId);
                }
            }

            $preparedPolls->delete();
        }
    }

    private function getChannelName(): string
    {
        $channelName = (new RequestRepository($this->request))->getDto()->getText();
        if (str_contains($channelName, 'https://t.me/')) {
            return "@" . substr($channelName, 13);
        }

        return '@' . ltrim($channelName, '@');
    }

    private function getChannelDto(): ChannelDto
    {
        $channelName = $this->getChannelName();
        $response = $this->senderService->getChatByChannelName($channelName);
        $data = json_decode($response, true);

        if (!array_key_exists('result', $data)) {
            throw new \Exception("Chat name not found");
        }

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
    }
}
