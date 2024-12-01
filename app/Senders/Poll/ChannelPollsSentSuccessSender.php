<?php

namespace App\Senders\Poll;

use App\Builder\Poll\PollBuilder;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Models\Poll;
use App\Repositories\RequestRepository;
use App\Senders\AbstractSender;
use Illuminate\Support\Facades\Log;

class ChannelPollsSentSuccessSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        // Send polls to channel
        $this->sendPollsToChannel();

        $text = StateEnum::CHANNEL_POLLS_SENT_SUCCESS->title();
        $buttons = [new ButtonDto("/" . CommandEnum::START->value, "Вернуться в начало")];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    private function normalizeChannelName(): string
    {
        $channelName = $this->getInput();
        if (str_contains($channelName, 'https://t.me/')) {
            return "@" . substr($channelName, 13);
        }

        return '@' . ltrim($channelName, '@');
    }

    private function sendPollsToChannel(): void
    {
        $channelName = $this->getInput();
        $preparedPolls = $this->user->preparedPolls()->first();

        if ($preparedPolls) {
            $pollIds = explode(',', $preparedPolls->poll_ids);

            foreach ($pollIds as $pollId) {
                if ($poll = Poll::where('tg_message_id', $pollId)->first()) {
                    $options = [];

                    foreach ($poll->options as $pollOption) {
                        $options[] = $pollOption->text;
                    }

                    Log::debug('ChannelPollsSentSuccessSender: ' . $poll->question);

                    $pollBuilder = $this->pollBuilder
                        ->setBuilder(new PollBuilder())
                        ->createPoll(
                            question: $poll->question,
                            options: $options,
                            isAnonymous: (bool)$poll->isAnonymous,
                            isQuiz: !$poll->allows_multiple_answers,
                            correctOptionId: $poll->correct_option_id,
                        );

                    $this->senderService->sendPoll($pollBuilder, $channelName);
                }
            }
        }
    }

    private function getInput(): string
    {
        return (new RequestRepository($this->request))->getDto()->getText();
    }

    // TODO: Write method for getting channel id
}
