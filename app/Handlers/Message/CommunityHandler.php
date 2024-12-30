<?php

namespace App\Handlers\Message;

use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use App\Enums\StateEnum;
use App\Models\Game;
use App\States\UserContext;
use Illuminate\Support\Facades\Log;

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

    private function getGame(MessageTextDto|MessagePhotoDto $dto): Game
    {
        $channelName = '@' . $dto->getChat()->getUsername();
        $messageId = $dto->getId();

        Log::debug('channel: ' . $channelName);
        Log::debug('message id: ' . $messageId);

        return Game::where('channel', $channelName)->where('message_id', $messageId)->first();
    }

    private function getMessageDto(): MessageTextDto|MessagePhotoDto
    {
        $dto = $this->repository->createDto();

        return method_exists($dto, 'getMessage')
            ? $dto->getMessage()
            : $this->repository->createDto();
    }
}
