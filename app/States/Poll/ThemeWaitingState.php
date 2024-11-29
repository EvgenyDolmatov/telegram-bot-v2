<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ThemeWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_THEME_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = StateEnum::POLL_AI_RESPONDED_CHOICE;

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->messageSender, $this->senderService);
        $sender->send();

        // Логика обработки темы для OpenAI и вывод ответа от AI
        // $context->setState(new NewState());
    }
}
