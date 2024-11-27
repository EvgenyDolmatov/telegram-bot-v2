<?php

namespace App\States;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Models\State;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractState implements UserState
{
    protected SenderService $senderService;
    protected MessageSender $messageSender;
    protected User $user;

    public function __construct(
        protected readonly Request $request,
        protected readonly TelegramService $telegramService
    ) {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());

        $this->user = User::getOrCreate(new RequestRepository($this->request));
    }

    public function handleCommand(string $command, UserContext $context): void
    {
        $user = $this->user;
        $command = $this->updateState($command, $context);
        $newState = State::where('code', $command)->first();

        switch ($command) {
            case CommandEnum::ACCOUNT->value:
                $this->addToTrash();

                $text = "Мой аккаунт:";
                $buttons = [
                    new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRED_USERS->value, 'Приглашенные пользователи'),
                    new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRAL_LINK->value, 'Моя реферальная ссылка'),
                    new ButtonDto(CommandEnum::START->value, 'Назад'),
                ];

                $message = $this->messageSender->createMessage($text, $buttons);
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::ADMIN->value:
                $this->addToTrash();

                if ($user->is_admin) {
                    $text = "Меню администратора:";
                    $buttons = [
                        new ButtonDto(CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value, 'Создать рассылку'),
                        new ButtonDto(CommonCallbackEnum::ADMIN_STATISTIC_MENU->value, 'Статистика бота'),
                        new ButtonDto(CommandEnum::START->value, 'Вернуться в начало')
                    ];

                    $message = $this->messageSender->createMessage($text, $buttons);
                    $this->senderService->sendMessage($message);
                    return;
                }

                $this->someProblemMessage();

                break;
            case CommandEnum::CHANNEL->value:
                $this->addToTrash();

                $message = $this->messageSender->createMessage('Channel');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::HELP->value:
                $this->addToTrash();

                $text = "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>";
                $message = $this->messageSender->createMessage(
                    text: $text,
                    buttons: [new ButtonDto(CommandEnum::START->value, 'Назад')]
                );
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::START->value:
                $this->addToTrash();

                $message = $this->messageSender->createMessage(
                    text: $newState->text,
                    buttons: $newState->prepareButtons($user)
                );

                $this->senderService->sendPhoto(
                    message: $message,
                    imageUrl: asset('assets/img/start.png')
                );

                break;
        }
    }

    abstract public function handleInput(string $input, UserContext $context): void;

    protected function updateState(string $command, UserContext $context): string
    {
        $command = $this->clearCommand($command);
        $newState = CommandEnum::from($command);

        $context->setState($newState->userState($this->request, $this->telegramService));

        $currentState = State::where('code', $command)->first();
        $this->user->states()->detach();
        $this->user->states()->attach($currentState->id);

        return $command;
    }

    protected function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }

    private function addToTrash(): void
    {
        $requestDto = (new RequestRepository($this->request))->getDto();
        TrashMessage::add($requestDto->getChat()->getId(), $requestDto->getId(), true);
    }

    private function someProblemMessage(): void
    {
        $text = "Что-то пошло не так. Попробуйте еще раз...";
        $buttons = [new ButtonDto(CommandEnum::START->value, 'Начать сначала')];

        $message = $this->messageSender->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }
}
