<?php

namespace App\Handlers\Message;

use App\Dto\Telegram\MessageDto;
use App\Enums\StateEnum;
use App\Models\Game;
use App\States\UserContext;

class CommunityHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        $game = $this->getGame($this->getMessageDto());
        $organizer = $game->organizer;

        $state = StateEnum::from($organizer->getCurrentState()->code);
        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleInput($message);
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
