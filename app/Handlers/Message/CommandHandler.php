<?php

namespace App\Handlers\Message;

use App\Commands\CommandContainer;
use App\Enums\CommandEnum;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class CommandHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        $message = $this->clearCommand($message);

        $command = CommandContainer::retrieve($message);
        $command->execute($this->senderService);


//        $helper = $this->helper;

//        switch ($message) {
//            case CommandEnum::START->value:
//                if ($helper->canContinue()) {
//                    $helper->mainChoice();
//                    $this->user->changeState($this->request);
//                    return;
//                }
//
//                $helper->subscribeToCommunity();
//                return;
//            case CommandEnum::HELP->value:
//                $helper->help();
//                return;
//            case CommandEnum::ACCOUNT->value:
//                $helper->account();
//                return;
//            case CommandEnum::ADMIN->value:
//                $helper->adminMenu();
//                return;
//            default:
//                $helper->someProblemMessage();
//        }
    }

    /**
     * Remove from "/start" command referral code
     *
     * @param string $message
     * @return string
     */
    private function clearCommand(string $message): string
    {
        if (str_starts_with($message, CommandEnum::START->value) && str_contains($message, ' ')) {
            $messageData = explode(' ', $message);
            $message = $messageData[0];
        }

        return ltrim($message, '/');
    }
}
