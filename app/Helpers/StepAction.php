<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Constants\CallbackConstants;
use App\Constants\CommandConstants;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Dto\ButtonDto;
use App\Dto\OpenAiCompletionDto;
use App\Models\AiRequest;
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
        $this->pollSender = new PollSender();
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
     * @param bool $isTrash
     * @return void
     */
    public function sendMessage(string $text, ?array $buttons = null, bool $isTrash = true): void
    {
        $message = $buttons === null
            ? $this->messageSender->createSimpleMessage($text)
            : $this->messageSender->createMessageWithButtons($text, $buttons);

        $this->prepareMessageData()->sendMessage($message, $isTrash);
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
     * @param string|null $correctOptionId
     * @param bool $isTrash
     * @return void
     */
    public function sendPoll(
        string  $question,
        array   $options,
        bool    $isAnonymous,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        bool    $isTrash = true
    ): void
    {
        $poll = $this->pollSender
            ->setBuilder(new PollBuilder())
            ->createPoll($question, $options, $isAnonymous, $isQuiz, $correctOptionId);

        $this->preparePollData()->sendPoll($poll, $isTrash);
    }

    public function addToTrash(): void
    {
        $repository = $this->repository;
        $chatDto = $repository->convertToChat();
        $messageDto = $repository->convertToMessage();

        TrashMessage::add($chatDto, $messageDto, true);
    }

    public function canContinue(): bool
    {
        $user = User::getOrCreate($this->repository);
        $aiRequest = AiRequest::where('tg_chat_id', $user->tg_chat_id)->get();

        // TODO: remove this
        return true;
//        return $aiRequest->count() && $this->prepareMessageData()->isMembership();
    }

    /**
     * If user pressed "/start" button
     *
     * @return void
     */
    public function mainChoice(): void
    {
        $this->addToTrash();

        $repository = $this->repository;
        $user = User::getOrCreate($repository);
        $startState = State::where('code', StateConstants::START)->first();

        $this->sendMessage(
            text: $startState->text,
            buttons: $startState->prepareButtons($user)
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
        $currentState = $user->getCurrentState();

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
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
        $currentState = $user->getCurrentState();

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
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
        $currentState = $user->getCurrentState();

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
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
        $currentState = $user->getCurrentState();

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
        );
    }

    /**
     * If user pressed to "sector" button
     * Show all subjects
     *
     * @return void
     */
    public function selectSubject(): void
    {
        $user = User::getOrCreate($this->repository);
        $currentState = $user->getCurrentState();

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
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
        $currentState = $user->getCurrentState();

        $flow = $user->getFlowData();
        if (isset($flow[StateConstants::SUBJECT_CHOICE])) {
            $subject = Subject::where('code', $flow[StateConstants::SUBJECT_CHOICE])->first();

            if ($subject->has_child) {
                $previousState = $user->getPrevState();

                $user->states()->detach();
                $user->states()->attach($previousState->id);

                $this->selectSubject();
                return;
            }
        }

        $this->sendMessage(
            text: $currentState->text,
            buttons: $currentState->prepareButtons($user, true)
        );
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
        $currentState = $user->getCurrentState();

        // Выводим сообщение
        $this->sendMessage($currentState->text);

        $openAiService = new OpenAiService($user);
        $openAiRepository = new OpenAiRepository($openAiService);

        /** @var OpenAiCompletionDto $openAiCompletion */
        $openAiCompletion = $openAiRepository->getCompletion();

        $flow = $user->getOpenedFlow();

        if ($openAiCompletion) {
            if ($questions = $openAiCompletion->getQuestions()) {
                $correctAnswers = '';
                $questionNumber = 0;
                foreach ($questions as $question) {
                    $this->sendPoll(
                        question: $question->getText(),
                        options: $question->getOptions(),
                        isAnonymous: $flow->isAnonymous(),
                        isQuiz: $flow->isQuiz(),
                        correctOptionId: $question->getAnswer(),
                        isTrash: false
                    );

                    if ($flow->isQuiz()) {
                        $questionNumber++;
                        $questionText = trim($question->getText(), ':');
                        $correctAnswers .= "\n\nВопрос № $questionNumber. $questionText";
                        $correctAnswers .= "\nПравильный ответ: {$question->getOptions()[$question->getAnswer()]}";
                    }
                }

                // Show right answers
                if ($correctAnswers !== '') {
                    $this->sendMessage($correctAnswers, null, false);
                }
            }

            // Save result to DB
            AiRequest::create([
                'tg_chat_id' => $user->tg_chat_id,
                'user_flow_id' => $flow->id,
                'ai_survey' => json_encode(array_map(fn($question) => [
                    'text' => $question->getText(),
                    'options' => $question->getOptions(),
                    'answer' => $question->getAnswer(),
                ], $openAiCompletion->getQuestions())),
                'usage_prompt_tokens' => $openAiCompletion->getUsage()->getPromptTokens(),
                'usage_completion_tokens' => $openAiCompletion->getUsage()->getCompletionTokens(),
                'usage_total_tokens' => $openAiCompletion->getUsage()->getTotalTokens(),
            ]);

            // Close current flow
            $flow->is_completed = 1;
            $flow->save();

            if (!$this->canContinue()) {
                $this->subscribeToCommunity();
                return;
            }

            // Show message about next action
            $message = "Выберите, что делать дальше:";
            $buttons = [
                new ButtonDto(
                    callbackData: CommandConstants::START,
                    text: 'Выбрать другую тему'
                ),
                new ButtonDto(
                    callbackData: CallbackConstants::REPEAT_FLOW,
                    text: 'Создать еще 5 вопросов'
                )
            ];

            $this->sendMessage($message, $buttons);
        }
    }

    public function subscribeToCommunity(): void
    {
        $message = "Подпишись на <a href='https://t.me/corgish_ru'>наш канал</a>, чтобы продолжить...";
        $this->sendMessage(
            $message,
            [new ButtonDto(CommandConstants::START, 'Я подписался')]
        );
    }
}
