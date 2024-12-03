<?php

namespace App\States\Poll;

use App\Constants\CommonConstants;
use App\Enums\StateEnum;
use App\Models\Sector;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class SectorChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_SECTOR_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CommonConstants::BACK) {
            return $baseState->backState();
        }

        return Sector::where('code', $input)->first()
            ? StateEnum::POLL_SUBJECT_CHOICE
            : self::STATE; // TODO: Need to send error message if sector does not exist
    }
}