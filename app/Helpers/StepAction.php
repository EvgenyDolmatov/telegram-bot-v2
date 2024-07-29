<?php

namespace App\Helpers;

use App\Builder\Message\Message;
use App\Builder\Message\MessageBuilder;
use App\Builder\Sender;
use App\Constants\ButtonConstants;
use App\Constants\ButtonKeyConstants;
use App\Constants\StateConstants;
use App\Models\Sector;
use App\Models\TrashMessage;
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

    public function prepareData(Message $message): SendMessageService
    {
        return new SendMessageService($this->request, $this->telegramService, $message);
    }

    /**
     * If user pressed "/start" button
     *
     * @return void
     */
    public function start(): void
    {
        $repository = $this->repository;
        $chatDto = $this->repository->convertToChat();
        $messageDto = $this->repository->convertToMessage();

        $user = User::getOrCreate($repository);
        $user->changeState(StateConstants::START);

        TrashMessage::add($chatDto, $messageDto, true);

        // Prepare to send message
        $text = 'Привет! Выбери вариант:';
        $buttons = [
            ButtonConstants::CREATE_SURVEY,
            ButtonConstants::SUPPORT
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        $this->prepareData($message)->send();
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
        $this->prepareData($message)->send();
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
        $this->prepareData($message)->send();
    }

    /**
     * If user pressed to "create survey" button
     * Show survey type choice
     *
     * @return void
     */
    public function selectSurveyType(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState(StateConstants::TYPE_CHOICE);

        $text = 'Выберите тип опроса:';
        $buttons = [
            ButtonConstants::QUIZ,
            ButtonConstants::SURVEY
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        $this->prepareData($message)->send();
    }

    /**
     * If user pressed to "type_quiz" or "type_survey" button
     * Show is anon choice
     *
     * @return void
     */
    public function selectAnonymity(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState(StateConstants::ANON_CHOICE);

        $text = 'Опрос будет анонимный?';
        $buttons = [
            ButtonConstants::IS_ANON,
            ButtonConstants::IS_NOT_ANON
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        $this->prepareData($message)->send();
    }

    /**
     * If user pressed to "is_anon" or "is_not_anon" button
     * Show all sectors
     *
     * @return void
     */
    public function selectSector(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState(StateConstants::SECTOR_CHOICE);

        $text = 'Выберите направление:';
        $buttons = [];

        foreach (Sector::all() as $sector) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $sector->title,
                ButtonKeyConstants::CALLBACK => $sector->code
            ];
        }

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        $this->prepareData($message)->send();
    }

    /**
     * If user pressed to "sector" button
     * Show all subjects
     *
     * @return void
     */
    public function selectSubject(): void
    {
        //
    }
}
