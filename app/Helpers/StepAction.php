<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\Sender;
use App\Constants\ButtonConstants;
use App\Models\State;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SendMessageService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class StepAction
{
    private Sender $sender;
    private TelegramService $telegramService;
    private Request $request;
    private RequestRepository $repository;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->telegramService = $telegramService;
        $this->request = $request;
        $this->sender = (new Sender())->setBuilder(new MessageBuilder());
        $this->repository = new RequestRepository($request);
    }

    /**
     * If user pressed "/start" button
     *
     * @return void
     */
    public function start(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState('start');

        $text = 'Привет! Выбери вариант:';
        $buttons = [
            ButtonConstants::CREATE_SURVEY,
            ButtonConstants::SUPPORT
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }

    /**
     * If user pressed "/help" button
     *
     * @return void
     */
    public function help(): void
    {
        $text = 'Инструкция по работе с ботом:';

        $message = $this->sender->createSimpleMessage($text);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }

    /**
     * If user pressed to "support" button
     *
     * @return void
     */
    public function support(): void
    {
        $text = 'Если у вас есть вопросы, напишите мне в личные сообщения: <a href="https://t.me/nkm_studio">https://t.me/nkm_studio</a>';

        $message = $this->sender->createSimpleMessage($text);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }

    /**
     * If user pressed to "create survey" button
     *
     * @return void
     */
    public function selectSurveyType(): void
    {
        $text = 'Выберите тип опроса:';
        $buttons = [
            ButtonConstants::QUIZ,
            ButtonConstants::SURVEY
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }
}
