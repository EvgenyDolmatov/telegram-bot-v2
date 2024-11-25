<?php

namespace App\Handlers\Message;

use App\Dto\ButtonDto;
use App\Enums\SurveyCallbackEnum;
use App\Models\PreparedPoll;
use App\Models\User;
use App\Models\UserFlow;
use App\Repositories\RequestRepository;
use App\Services\StateService;

class StateHandler extends AbstractHandler
{
    /**
     * @throws \Exception
     */
    public function handle(string $message): void
    {
        $request = $this->request;
        $helper = $this->helper;

        $requestRepository = new RequestRepository($request);
        $user = User::getOrCreate($requestRepository);

        if ($message !== "polls_chosen" && !str_starts_with($message, 'poll_')) {
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
                    callbackData: "poll_{$poll->tg_message_id}",
                    text: $poll->question
                );
            }

            $buttons[] = new ButtonDto(
                "polls_chosen",
                'Готово ➡️'
            );

            $helper->sendMessage($text, $buttons);
        }

        /**
         * TODO: >>
         *
         * Create or update prepared polls
         */
        if (str_starts_with($message, 'poll_')) {

            $pollId = substr($message, 5);
            $preparedPoll =
                PreparedPoll::where('user_id', $user->id)->first() ??
                PreparedPoll::create(['user_id' => $user->id]);

            $currentPollIds = explode(',', $preparedPoll->poll_ids);

            $newPollIds = in_array($pollId, $currentPollIds)
                ? array_diff($currentPollIds, [$pollId])
                : array_merge($currentPollIds, [$pollId]);

            $preparedPoll->update(['poll_ids' => trim(implode(',', $newPollIds),',')]);


            $text = "Выберите, какие вопросы нужно отправить?";
            $buttons = [];

            $latestPolls = $user->polls()->latest()->take(5)->get();
            foreach ($latestPolls as $poll) {
                $question = $poll->question;

                if (in_array($poll->tg_message_id, $newPollIds)) {
                    $question = "✅ $question";
                }

                $buttons[] = new ButtonDto(
                    callbackData: "poll_{$poll->tg_message_id}",
                    text: $question
                );
            }

            $buttons[] = new ButtonDto(
                "polls_chosen",
                'Готово ➡️'
            );

//            $helper->editMessage('', '', '');
        }

        // TODO: Need to do something with index 9...
        if ($user->states->contains(9)) {
            $helper->adminNewsletterConfirmation();
        }
    }
}
