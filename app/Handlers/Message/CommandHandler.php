<?php

namespace App\Handlers\Message;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\PollSender;
use App\Enums\CommandEnum;
use App\Services\SenderService;
use App\Services\TelegramService;
use App\States\StartState;
use App\States\UserContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommandHandler extends AbstractHandler
{
    private MessageSender $messageSender;
    private SenderService $senderService;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
    }

    /**
     * @throws \Exception
     */
    public function handle(string $message): void
    {
        $command = $this->clearCommand($message);
        $userContext = new UserContext(new StartState());
        $userContext->handleCommand($command);

        $message = $this->messageSender->createMessage('Start');
        $this->senderService->sendMessage($message);






        $helper = $this->helper;

        switch ($command) {
            case CommandEnum::START->value:
                if ($helper->canContinue()) {
                    $helper->mainChoice();
                    $this->user->changeState($this->request);
                    return;
                }

                $helper->subscribeToCommunity();
                return;
            case CommandEnum::HELP->value:
                $helper->help();
                return;
            case CommandEnum::ACCOUNT->value:
                $helper->account();
                return;
            case CommandEnum::ADMIN->value:
                $helper->adminMenu();
                return;
            case CommandEnum::CHANNEL->value:
                $messageData = $this->getMessageData($message);
                $helper->sendToChannel($messageData);

                return;
            default:
                $helper->someProblemMessage();
        }
    }

    /**
     * Remove from "/start" command referral code
     *
     * @param string $message
     * @return string
     */
    private function clearCommand(string $message): string
    {
        return explode(' ', ltrim($message, '/'))[0] ?? $message;
    }

    private function getMessageData(string $message): array
    {
        $data = explode(' ', $message);

        return [
            'command' => $data[0] ?? null,
            'parameter' => $data[1] ?? null,
            'arguments' => $data[2] ?? null
        ];
    }
}
