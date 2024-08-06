<?php

namespace App\Helpers;

use App\Builder\Message\Message;
use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\Poll;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Constants\ButtonConstants;
use App\Constants\ButtonKeyConstants;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Constants\TransitionConstants;
use App\Models\Sector;
use App\Models\State;
use App\Models\Subject;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\OpenAiRepository;
use App\Repositories\RequestRepository;
use App\Services\OpenAiService;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StepAction implements StepConstants
{
    private MessageSender $messageSender;
    private PollSender $pollSender;
    private TelegramService $telegramService;
    private Request $request;
    private RequestRepository $repository;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->telegramService = $telegramService;
        $this->request = $request;
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
        $this->pollSender = (new PollSender())->setBuilder(new PollBuilder());
        $this->repository = new RequestRepository($request);
    }

    /**
     * Prepare data to sending message
     *
     * @return SenderService
     */
    public function prepareMessageData(): SenderService
    {
        return new SenderService($this->request, $this->telegramService);
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
            ? $this->messageSender->createSimpleMessage($text)
            : $this->messageSender->createMessageWithButtons($text, $buttons);

        $this->prepareMessageData()->sendMessage($message);
    }

    /**
     * Prepare data to sending poll
     *
     * @return SenderService
     */
    public function preparePollData(): SenderService
    {
        return new SenderService($this->request, $this->telegramService);
    }

    /**
     * Send poll message
     *
     * @param string $question
     * @param array $options
     * @param bool $isAnonymous
     * @param bool $isQuiz
     * @param int|null $correctOptionId
     * @return void
     */
    public function sendPoll(
        string $question,
        array  $options,
        bool   $isAnonymous,
        bool   $isQuiz = false,
        ?int    $correctOptionId = null
    ): void
    {
        $poll = $isQuiz
            ? $this->pollSender->createQuiz($question, $options, $isAnonymous, $correctOptionId)
            : $this->pollSender->createPoll($question, $options, $isAnonymous);

        $this->preparePollData()->sendPoll($poll);
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
        $startState = State::where('code', StateConstants::START)->first();

        $this->sendMessage(
            text: $startState->text,
            buttons: $startState->prepareButtons()
        );
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
        $nextState = $user->getNextState();

        $this->sendMessage(
            text: $nextState->text,
            buttons: $nextState->prepareButtons()
        );
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
        $nextState = $user->getNextState();

        $this->sendMessage(
            text: $nextState->text,
            buttons: $nextState->prepareButtons()
        );
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
        $nextState = $user->getNextState();

        $this->sendMessage(
            text: $nextState->text,
            buttons: $nextState->prepareButtons()
        );
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
        $nextState = $user->getNextState();

        $buttons = [];
        foreach (Sector::all() as $sector) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $sector->title,
                ButtonKeyConstants::CALLBACK => $sector->code
            ];
        }

        $this->sendMessage(
            text: $nextState->text,
            buttons: $buttons
        );
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
        $nextState = $user->getNextState();

        $buttons = [];
        $subjects = $sector->subjects->where('parent_id', null);

        foreach ($subjects as $subject) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $subject->title,
                ButtonKeyConstants::CALLBACK => $subject->code
            ];
        }

        $this->sendMessage(
            text: $nextState->text,
            buttons: $buttons
        );
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
        $nextState = $user->getNextState();

        $buttons = [];
        $childSubjects = Subject::where('parent_id', $subject->id)->get();

        foreach ($childSubjects as $childSubject) {
            $buttons[] = [
                ButtonKeyConstants::TEXT => $childSubject->title,
                ButtonKeyConstants::CALLBACK => $childSubject->code
            ];
        }

        $this->sendMessage(
            text: $nextState->text,
            buttons: $buttons
        );
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
        $nextState = $user->getNextState();

        $this->sendMessage($nextState->text);
    }

    /**
     * If user sent custom request
     * Send data to Open AI
     *
     * @return void
     */
    public function responseFromAi(): void
    {
        // Обрабатываем
        $user = User::getOrCreate($this->repository);
//        $user->changeState($this->request);
//        $openAiService = new OpenAiService();
//        $openAiRepository = new OpenAiRepository($openAiService);
//        $openAiCompletion = $openAiRepository->getCompletion();



        // Выводим сообщение
        $this->sendMessage('Вы получили ответ от AI...');
        $this->sendPoll(
            question: 'TEST:',
            options: [
                ["text" => "Ответ 1"],
                ["text" => "Ответ 2"],
                ["text" => "Ответ 3"],
            ],
            isAnonymous: true
        );
    }
}
