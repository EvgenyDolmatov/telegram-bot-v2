<?php

namespace App\Handlers;

use App\Dto\Telegram\MessageDto;
use App\Handlers\Message\AbstractMessageHandler;
use App\Handlers\Message\CommandMessageHandler;
use App\Handlers\Message\StateMessageHandler;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;

class MessageStrategy
{
    private AbstractMessageHandler $handler;

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly RepositoryInterface $repository
    ) {
    }

    public function defineHandler(): self
    {
//        if ($this->repository instanceof PollAnswerRepository) {
//            $handler = new PollAnswerHandler($this->telegramService, $this->repository);
//            return $this->setHandler($handler);
//        }

//        $dto = $this->getMessageDto();
        $message = $this->getInput();

//        if (in_array($dto->getChat()->getType(), ['supergroup', 'channel'])) {
//            $handler = new CommunityHandler($this->telegramService, $this->repository);
//            return $this->setHandler($handler);
//        }

        if (str_starts_with($message, '/')) {
            $handler = new CommandMessageHandler($this->telegramService, $this->repository);
        } else {
            $handler = new StateMessageHandler($this->telegramService, $this->repository);
        }

        return $this->setHandler($handler);
    }

    public function process(): void
    {
        $this->handler->handle($this->getInput());
    }

    private function setHandler(AbstractMessageHandler $handler): self
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

    // TODO: Continue here... need to do (maybe) handler for poll_answer
    private function getMessageDto(): MessageDto
    {
        $dto = $this->repository->createDto();

        return method_exists($dto, 'getMessage')
            ? $dto->getMessage()
            : $this->repository->createDto();
    }
}
