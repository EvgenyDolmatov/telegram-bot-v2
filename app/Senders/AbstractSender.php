<?php

namespace App\Senders;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Dto\Telegram\MessageDto;
use App\Enums\CommandEnum;
use App\Exceptions\ResponseException;
use App\Models\AiRequest;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Client\Response;
use PHPUnit\Logging\Exception;

abstract class AbstractSender implements SenderInterface
{
    protected SenderService $senderService;
    protected MessageSender $messageBuilder;
    protected PollSender $pollBuilder;

    public function __construct(
        protected readonly RepositoryInterface $repository,
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
        $buttons = [new ButtonDto('/' . CommandEnum::Start->value, 'Начать сначала')];

        $this->sendMessage($text, $buttons);
    }

    protected function subscribeToCommunity(): void
    {
        $text = "Подпишись на <a href='https://t.me/corgish_ru'>наш канал</a>, чтобы продолжить...";
        $buttons = [new ButtonDto('/' . CommandEnum::Start->value, 'Я подписался')];

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

    protected function getMessageDto(): MessageDto
    {
        $dto = $this->repository->createDto();

        return method_exists($dto, 'getMessage')
            ? $dto->getMessage()
            : $this->repository->createDto();
    }

    protected function getInputText(): string
    {
        $dto = $this->repository->createDto();

        if (method_exists($dto, 'getPhoto')) {
            return $dto->getCaption() ?? "";
        }

        if (method_exists($dto, 'getData')) {
            return $dto->getData();
        }

        return $dto->getText();
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

    protected function sendPoll(
        string  $question,
        array   $options,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        ?int    $chatId = null,
        bool    $isTrash = true
    ): Response {
        try {
            // Send poll message
            $pollBuilder = $this->pollBuilder
                ->setBuilder(new PollBuilder())
                ->createPoll($question, $options, $isQuiz, $correctOptionId);

            $response = $this->senderService->sendPoll($pollBuilder, $chatId, $isTrash);
        } catch (\Throwable $exception) {
            throw new Exception('An error occurred while submitting the poll');
        }

        return $response;
    }
}
