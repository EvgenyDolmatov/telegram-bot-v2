<?php

namespace App\Senders;

use App\Builder\MessageSender;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Models\State;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use Illuminate\Http\Request;

abstract class AbstractSender implements SenderInterface
{
    protected User $user;

    public function __construct(
        protected readonly Request       $request,
        protected readonly MessageSender $messageBuilder,
        protected readonly SenderService $senderService
    ) {
        $requestRepository = new RequestRepository($this->request);
        $this->user = User::getOrCreate($requestRepository);
    }

    abstract public function process(): void;

    protected function addToTrash(): void
    {
        $requestDto = (new RequestRepository($this->request))->getDto();
        TrashMessage::add($requestDto->getChat()->getId(), $requestDto->getId(), true);
    }

    protected function someProblemMessage(): void
    {
        $text = "Что-то пошло не так. Попробуйте еще раз...";
        $buttons = [new ButtonDto(CommandEnum::START->value, 'Начать сначала')];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }
}
