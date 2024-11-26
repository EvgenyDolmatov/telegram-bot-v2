<?php

namespace App\Handlers\Message;

use App\Dto\ButtonDto;
use App\Enums\SurveyCallbackEnum;
use App\Models\Poll;
use App\Models\PreparedPoll;
use App\Models\User;
use App\Models\UserFlow;
use App\Repositories\ChannelRepository;
use App\Repositories\RequestRepository;
use App\Services\StateService;
use Illuminate\Support\Facades\Log;

class StateHandler extends AbstractHandler
{
    private const string POLL_PREFIX = 'poll_';

    /**
     * @throws \Exception
     */
    public function handle(string $message): void
    {
        $request = $this->request;
        $helper = $this->helper;

        $requestRepository = new RequestRepository($request);
        $user = User::getOrCreate($requestRepository);

        if ($message !== "polls_chosen" && !str_starts_with($message, self::POLL_PREFIX)) {
            foreach ($user->preparedPolls() as $preparedPoll) {
                $preparedPoll->delete();
            }
        }

        $stateService = new StateService($request, $user, $helper, $message);
        $stateService->switchState();

        if ($message === SurveyCallbackEnum::REPEAT_FLOW->value) {
            $lastFlow = $user->getLastFlow();

            if ($lastFlow) {
                UserFlow::create([
                    'user_id' => $lastFlow->user_id,
                    'flow' => $lastFlow->flow,
                    'is_completed' => 0,
                ]);

                $helper->responseFromAi();
            }

            // TODO: Create some message about quiz repeat...
        }

        /**
         * Choosing polls for sending to own channel
         */
        if ($message === SurveyCallbackEnum::SEND_TO_CHANNEL->value) {
            $text = "Выберите, какие вопросы нужно отправить?";
            $buttons = [];

            $latestPolls = $user->polls()->latest()->take(5)->get();
            foreach ($latestPolls as $poll) {
                $buttons[] = new ButtonDto(
                    callbackData: self::POLL_PREFIX . $poll->tg_message_id,
                    text: $poll->question
                );
            }

            $buttons[] = new ButtonDto(
                "polls_chosen",
                'Готово ➡️'
            );

            $response = $helper->sendMessage($text, $buttons);
            $data = json_decode($response, true);

            $preparedPoll = PreparedPoll::where('user_id', $user->id)->first();
            if ($preparedPoll) {
                $preparedPoll->update([
                    'poll_ids' => null,
                    'tg_message_id' => (int)$data['result']['message_id'],
                    'channel' => null,
                ]);
            } else {
                PreparedPoll::create([
                    'user_id' => $user->id,
                    'tg_message_id' => (int)$data['result']['message_id']
                ]);
            }
        }

        /**
         * Create or update prepared polls
         */
        if (str_starts_with($message, self::POLL_PREFIX)) {
            $pollId = substr($message, 5);
            $preparedPoll = PreparedPoll::where('user_id', $user->id)->first();

            $currentPollIds = explode(',', $preparedPoll->poll_ids);

            $newPollIds = in_array($pollId, $currentPollIds)
                ? array_diff($currentPollIds, [$pollId])
                : array_merge($currentPollIds, [$pollId]);

            $preparedPoll->update(['poll_ids' => trim(implode(',', $newPollIds),',')]);


            $text = "Выберите, какие вопросы нужно отправить?";
            $buttons = [];

            $latestPolls = $user->polls()->latest()->take(5)->get();
            foreach ($latestPolls as $poll) {
                if (in_array($poll->tg_message_id, $newPollIds)) {
                    $question = "✅ " . $poll->question;
                } else {
                    $question = "❌ " . $poll->question;
                }

                $buttons[] = new ButtonDto(
                    callbackData: self::POLL_PREFIX . $poll->tg_message_id,
                    text: $question
                );
            }

            $buttons[] = new ButtonDto(
                SurveyCallbackEnum::POLLS_CHOSEN->value,
                'Готово ➡️'
            );

            $helper->editMessage(
                messageId: $preparedPoll->tg_message_id,
                text: $text,
                buttons: $buttons
            );
        }

        /**
         * Type channel's name which need to send polls
         */
        if ($message === SurveyCallbackEnum::POLLS_CHOSEN->value) {
            $text = "Введите название канала или ссылку на канал, куда нужно отправить выбранные тесты:";

            $helper->sendMessage($text);
        }


        $preparedPoll = $user->preparedPolls()->first();
        if ($preparedPoll && $preparedPoll->poll_ids && !$preparedPoll->channel && !str_starts_with($message, self::POLL_PREFIX)) {
            $requestDto = (new RequestRepository($request))->getDto();
            $channelName = $requestDto->getText();

            Log::debug('CHANNEL: ' . $channelName);

            if (str_starts_with($channelName, 'https://t.me/')) {
                $channelName = "@" . substr($channelName, 13);
            } elseif (str_starts_with($channelName, '@')) {
                $channelName = "@" . substr($channelName, 1);
            }

            Log::debug('Corrected CHANNEL: ' . $channelName);

            $preparedPoll->update(['channel' => $channelName]);

            // Send polls to channel
            $channelResponse = $helper->getChatByChannelName($channelName);
            $channelDto = (new ChannelRepository($channelResponse))->getDto();

            Log::debug('CHANNEL: ' . $channelResponse);

            $messageIds = explode(',', $preparedPoll->poll_ids);
            foreach ($messageIds as $messageId) {
                $poll = Poll::where('tg_message_id', $messageId)->first();
                $correctOptionLetters = ['a', 'b', 'c', 'd'];
                $options = [];

                foreach ($poll->options as $option) {
                    $options[] = $option->text;
                }

                $helper->sendPoll(
                    question: $poll->question,
                    options: $options,
                    isAnonymous: $poll->is_anonymous,
                    isQuiz: !$poll->allows_multiple_answers,
                    correctOptionId: $correctOptionLetters[$poll->correct_option_id],
                    chatId: $channelDto->getId(),
                    isTrash: false
                );
            }

            $text = "Тесты отправлены в канал $channelName";
            $helper->sendMessage($text);
        }

        // TODO: Need to do something with index 9...
        if ($user->states->contains(9)) {
            $helper->adminNewsletterConfirmation();
        }
    }
}
