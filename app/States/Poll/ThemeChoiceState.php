<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\Enums\ThemeEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ThemeChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::PollThemeChoice;
    private const StateEnum NEXT_STATE = StateEnum::PollRequestWaiting;

    public function handleInput(string $input, UserContext $context): void
    {
        // If unexpected callback, staying at current step
        $availableValues = $this->getAvailableCallbackValues(self::STATE);
        if (!empty($availableValues) && !in_array($input, $availableValues)) {
            $this->sendMessage(self::STATE);
            return;
        }

        // Move to the next step
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState(self::NEXT_STATE, $context);
        $this->sendMessage(self::NEXT_STATE);
    }

    protected function getAvailableCallbackValues(StateEnum $baseState): array
    {
        return array_map(fn ($theme) => $theme->value, ThemeEnum::cases());
    }
}
