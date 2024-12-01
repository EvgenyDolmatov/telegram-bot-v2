<?php

namespace App\Senders;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\PollSender;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Models\AiRequest;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractSender implements SenderInterface
{
    protected SenderService $senderService;
    protected MessageSender $messageBuilder;
    protected PollSender $pollBuilder;

    public function __construct(
        protected readonly Request         $request,
        protected readonly TelegramService $telegramService,
        protected readonly User            $user
    ) {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageBuilder = (new MessageSender())->setBuilder(new MessageBuilder());
        $this->pollBuilder = new PollSender();
    }

    abstract public function send(): void;

    protected function addToTrash(): void
    {
        $requestDto = (new RequestRepository($this->request))->getDto();
        TrashMessage::add($requestDto->getChat()->getId(), $requestDto->getId(), true);
    }

    protected function someProblemMessage(): void
    {
        $text = "Что-то пошло не так. Попробуйте еще раз...";
        $buttons = [new ButtonDto('/' . CommandEnum::START->value, 'Начать сначала')];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    protected function subscribeToCommunity(): void
    {
        $text = "Подпишись на <a href='https://t.me/corgish_ru'>наш канал</a>, чтобы продолжить...";
        $buttons = [new ButtonDto('/' . CommandEnum::START->value, 'Я подписался')];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }

    protected function canContinue(): bool
    {
        $aiRequest = AiRequest::where('tg_chat_id', $this->user->tg_chat_id)->get();
        if ($aiRequest->count()) {
            return $this->senderService->isMembership();
        }

        return true;
    }

    protected function getInputText(): string
    {
        return (new RequestRepository($this->request))->getDto()->getText();
    }
}
