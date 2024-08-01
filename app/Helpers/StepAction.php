<?php

namespace App\Helpers;

use App\Builder\Message\Message;
use App\Builder\Message\MessageBuilder;
use App\Builder\Sender;
use App\Constants\ButtonConstants;
use App\Constants\ButtonKeyConstants;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Constants\TransitionConstants;
use App\Models\Sector;
use App\Models\Subject;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SendMessageService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class StepAction implements StepConstants
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
     * Prepare data to sending message
     *
     * @param Message $message
     * @return SendMessageService
     */
    public function prepareData(Message $message): SendMessageService
    {
        return new SendMessageService($this->request, $this->telegramService, $message);
    }

    /**
     * Send simple message or message with buttons
     *
     * @param string $text
     * @param array|null $buttons
     * @return void
     */
    public function sendMessage(string $text, ?array $buttons = null): void
    {
        $message = $buttons === null
            ? $this->sender->createSimpleMessage($text)
            : $this->sender->createMessageWithButtons($text, $buttons);

        $this->prepareData($message)->send();
    }

    public function addToTrash(): void
    {
        $repository = $this->repository;
        $chatDto = $repository->convertToChat();
        $messageDto = $repository->convertToMessage();

        TrashMessage::add($chatDto, $messageDto, true);
    }

    /**
     * If user pressed "/start" button
     *
     * @return void
     */
    public function start(): void
    {
        $this->addToTrash();

        $repository = $this->repository;
        $user = User::getOrCreate($repository);
        $user->changeState($this->request, StateConstants::START);

        // Prepare to send message
        $buttons = [
            ButtonConstants::CREATE_SURVEY,
            ButtonConstants::SUPPORT
        ];

        $this->sendMessage(self::START_TEXT, $buttons);
    }

    /**
     * If user pressed "/help" button
     *
     * @return void
     */
    public function help(): void
    {
        $this->addToTrash();
        $this->sendMessage(self::HELP_TEXT);
    }

    /**
     * If user pressed to "support" button
     *
     * @return void
     */
    public function support(): void
    {
        $this->sendMessage(self::SUPPORT_TEXT);
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
        $user->changeState($this->request);

        $buttons = [
            ButtonConstants::QUIZ,
            ButtonConstants::SURVEY
        ];

        $this->sendMessage(self::SURVEY_TYPE_TEXT, $buttons);
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
        $user->changeState($this->request);

        $buttons = [
            ButtonConstants::IS_ANON,
            ButtonConstants::IS_NOT_ANON
        ];

        $this->sendMessage(self::ANONYMITY_TEXT, $buttons);
    }

    /**
     * If user pressed to "is_anon" or "is_not_anon" button
     * Show is difficulty choice
     *
     * @return void
     */
    public function selectDifficulty(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState($this->request);

        $buttons = [
            ButtonConstants::LEVEL_EASY,
            ButtonConstants::LEVEL_MIDDLE,
            ButtonConstants::LEVEL_HARD
        ];

        $this->sendMessage(self::DIFFICULTY_TEXT, $buttons);
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
        $user->changeState($this->request);

        $buttons = [];
        foreach (Sector::all() as $sector) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $sector->title,
                ButtonKeyConstants::CALLBACK => $sector->code
            ];
        }

        $this->sendMessage(self::SECTOR_TEXT, $buttons);
    }

    /**
     * If user pressed to "sector" button
     * Show all subjects
     *
     * @param Sector $sector
     * @return void
     */
    public function selectSubject(Sector $sector): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState($this->request);

        $buttons = [];
        $subjects = $sector->subjects->where('parent_id', null);

        foreach ($subjects as $subject) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $subject->title,
                ButtonKeyConstants::CALLBACK => $subject->code
            ];
        }

        $this->sendMessage(self::SUBJECT_TEXT, $buttons);
    }

    /**
     * If user pressed to "parent subject" button
     * Show child subjects
     *
     * @param Subject $subject
     * @return void
     */
    public function selectChildSubject(Subject $subject): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState($this->request, TransitionConstants::SOURCE);

        $buttons = [];

        $childSubjects = Subject::where('parent_id', $subject->id)->get();

        foreach ($childSubjects as $childSubject) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $childSubject->title,
                ButtonKeyConstants::CALLBACK => $childSubject->code
            ];
        }

        $this->sendMessage(self::SUBJECT_TEXT, $buttons);
    }

    /**
     * If user pressed to "subject" button
     * Waiting user request
     *
     * @return void
     */
    public function waitingThemeRequest(): void
    {
        $user = User::getOrCreate($this->repository);
        $user->changeState($this->request);

        $this->sendMessage(self::CUSTOM_TEXT);
    }
}
