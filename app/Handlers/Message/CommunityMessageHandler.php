<?php

namespace App\Handlers\Message;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Dto\Telegram\MessageDto;
use App\Models\Game;
use App\Services\SenderService;

class CommunityMessageHandler extends AbstractMessageHandler
{
    public function handle(string $message): void
    {
//        $game = $this->getGame($this->getMessageDto());
//        $organizer = $game->organizer;

//        $state = StateEnum::from($organizer->getCurrentState()->code);
//        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
//        $userContext->handleInput($message);

        $msg = (new MessageSender())->setBuilder(new MessageBuilder())->createMessage("Hello, Bro!");

        (new SenderService($this->repository, $this->telegramService))
            ->sendMessage($msg, false, -1002401365163);
    }

    private function getGame(MessageDto $dto): Game
    {
        $channelName = '@' . $dto->getChat()->getUsername();
        $messageId = $dto->getId();

        return Game::where('channel', $channelName)->where('message_id', $messageId)->first();
    }

    private function getMessageDto(): MessageDto
    {
        $dto = $this->repository->createDto();

        return method_exists($dto, 'getMessage')
            ? $dto->getMessage()
            : $this->repository->createDto();
    }
}
