<?php

namespace App\Handlers;

use App\Dto\Telegram\MessageDto;
use App\Handlers\Message\AbstractHandler;
use App\Handlers\Message\CommandHandler;
use App\Handlers\Message\CommunityHandler;
use App\Handlers\Message\StateHandler;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;

class MessageStrategy
{
    private AbstractHandler $handler;

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly RepositoryInterface $repository
    ) {
    }

    public function defineHandler(): self
    {
        $dto = $this->getMessageDto();
        $message = $this->getInput();

        if (in_array($dto->getChat()->getType(), ['supergroup', 'channel'])) {
            $handler = new CommunityHandler($this->telegramService, $this->repository);
            return $this->setHandler($handler);
        }

        if (str_starts_with($message, '/')) {
            $handler = new CommandHandler($this->telegramService, $this->repository);
        } else {
            $handler = new StateHandler($this->telegramService, $this->repository);
        }

        return $this->setHandler($handler);
    }

    public function process(): void
    {
        $this->handler->handle($this->getInput());
    }

    private function setHandler(AbstractHandler $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    private function getInput(): string
    {
        $dto = $this->repository->createDto();

        if (method_exists($dto, 'getText')) {
            return $dto->getText();
        }

        if (method_exists($dto, 'getData')) {
            return $dto->getData();
        }

        return "";
    }

    private function getMessageDto(): MessageDto
    {
        $dto = $this->repository->createDto();

        return method_exists($dto, 'getMessage')
            ? $dto->getMessage()
            : $this->repository->createDto();
    }
}
