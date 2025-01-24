<?php

namespace App\States\Poll;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Enums\ThemeEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Illuminate\Support\Facades\Log;

class ThemeChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::PollThemeChoice;

    public function handleInput(string $input, UserContext $context): void
    {
        $state = $this->getState($input, self::STATE);

        // If unexpected callback, staying at current step
        $availableValues = $this->getAvailableCallbackValues(self::STATE);
        if (!empty($availableValues) && !in_array($input, $availableValues)) {
            Log::debug('I am here ');
            $this->sendMessage(self::STATE);
            return;
        }

        // Move to the next step
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);
        $this->sendMessage($state);
    }

    protected function getAvailableCallbackValues(StateEnum $baseState): array
    {
        $callbacks = array_map(fn ($theme) => $theme->value, ThemeEnum::cases());
        $callbacks[] = CallbackEnum::Back->value;

        return $callbacks;
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return ThemeEnum::from($input)->toState();
    }
}
