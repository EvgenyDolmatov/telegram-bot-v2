<?php

namespace App\Senders;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\PollSender;
use App\Dto\ButtonDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use App\Enums\CommandEnum;
use App\Exceptions\ResponseException;
use App\Models\AiRequest;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Repositories\Telegram\AbstractRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Client\Response;

abstract class AbstractSender implements SenderInterface
{
    protected SenderService $senderService;
    protected MessageSender $messageBuilder;
    protected PollSender $pollBuilder;

    public function __construct(
        protected readonly AbstractRepository $repository,
        protected readonly TelegramService   $telegramService,
        protected readonly User              $user
    ) {
        $this->senderService = new SenderService($repository, $telegramService);
        $this->messageBuilder = (new MessageSender())->setBuilder(new MessageBuilder());
        $this->pollBuilder = new PollSender();
    }

    abstract public function send(): void;

    protected function addToTrash(): void
    {
        $messageDto = $this->getMessageDto();
        TrashMessage::add($messageDto->getChat()->getId(), $messageDto->getId(), true);
    }

    protected function someProblemMessage(): void
    {
        $text = "Что-то пошло не так. Попробуйте еще раз...";
        $buttons = [new ButtonDto('/' . CommandEnum::START->value, 'Начать сначала')];

        $this->sendMessage($text, $buttons);
    }

    protected function subscribeToCommunity(): void
    {
        $text = "Подпишись на <a href='https://t.me/corgish_ru'>наш канал</a>, чтобы продолжить...";
        $buttons = [new ButtonDto('/' . CommandEnum::START->value, 'Я подписался')];

        $this->sendMessage($text, $buttons);
    }

    protected function canContinue(): bool
    {
        $aiRequest = AiRequest::where('tg_chat_id', $this->user->tg_chat_id)->get();
        if ($aiRequest->count()) {
            return $this->senderService->isMembership();
        }

        return true;
    }

    protected function getMessageDto(): MessageTextDto|MessagePhotoDto
    {
        return $this->repository->getDto();
    }

    protected function getInputText(): string
    {
        return $this->getMessageDto()->getText();
    }

    /**
     * @param string $text
     * @param ButtonDto[]|null $buttons
     * @param bool $isTrash
     * @param int|null $chatId
     * @return Response
     * @throws \Exception
     */
    protected function sendMessage(
        string $text,
        ?array $buttons = null,
        bool   $isTrash = true,
        ?int   $chatId = null
    ): Response {
        $message = $this->messageBuilder->createMessage($text, $buttons);
        return $this->senderService->sendMessage($message, $isTrash, $chatId);
    }

    /**
     * @param int $messageId
     * @param int|null $chatId
     * @param string $text
     * @param ButtonDto[]|null $buttons
     * @return Response
     */
    protected function editMessage(int $messageId, string $text, ?array $buttons = null, ?int $chatId = null): Response
    {
        $message = $this->messageBuilder->createMessage($text, $buttons);
        return $this->senderService->editMessage($message, $messageId, $chatId);
    }

    /**
     * @param string $imageUrl
     * @param string $text
     * @param ButtonDto[]|null $buttons
     * @param bool $isTrash
     * @param int|null $chatId
     * @return Response
     * @throws ResponseException
     */
    protected function sendPhoto(
        string $imageUrl,
        string $text,
        ?array $buttons = null,
        bool   $isTrash = true,
        ?int   $chatId = null
    ): Response {
        $message = $this->messageBuilder->createMessage($text, $buttons);
        return $this->senderService->sendPhoto($message, $imageUrl, $isTrash, $chatId);
    }
}
