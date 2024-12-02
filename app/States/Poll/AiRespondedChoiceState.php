<?php

namespace App\States\Poll;

use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Models\UserFlow;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class AiRespondedChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_AI_RESPONDED_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step and update flow
        $this->flowHandler($input);
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }

    /**
     * Rules for flow update
     *
     * @param string $input
     * @return void
     */
    private function flowHandler(string $input): void
    {
        match ($input) {
            PollEnum::REPEAT_FLOW->value => $this->duplicateLastFlow(),
            default => $this->user->updateFlow(self::STATE, $input, true)
        };
    }

    private function duplicateLastFlow(): void
    {
        $lastFlow = $this->user->flows()->where('is_completed', true)->latest()->first();

        if ($lastFlow) {
            $data = $lastFlow->flow ? json_decode($lastFlow->flow, true): null;
            if ($data && array_key_exists(StateEnum::POLL_AI_RESPONDED_CHOICE->value, $data)) {
                unset($data[StateEnum::POLL_AI_RESPONDED_CHOICE->value]);
            }

            UserFlow::create([
                'user_id' => $lastFlow->user_id,
                'flow' => json_encode($data),
                'is_completed' => false,
            ]);
        }
    }
}
