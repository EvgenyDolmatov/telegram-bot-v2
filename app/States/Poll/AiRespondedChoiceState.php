<?php

namespace App\States\Poll;

use App\Enums\CallbackEnum;
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
        $this->sendMessage($state);
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
            CallbackEnum::RepeatFlow->value => $this->duplicateLastFlow(),
            default => $this->user->updateFlow(self::STATE, $input, true) // TODO: Remove and check
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
