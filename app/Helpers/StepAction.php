<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Dto\ButtonDto;
use App\Dto\OpenAiCompletionDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Enums\SurveyCallbackEnum;
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
    private SenderService $senderService;
    private RequestRepository $repository;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
        $this->pollSender = new PollSender();
        $this->repository = new RequestRepository($request);
    }

    /**
     * Send photo
     *
     * @param string $imageUrl
     * @param string $text
     * @param array|null $buttons
     * @param bool $isTrash
     * @return void
     */
    public function sendPhoto(string $imageUrl, string $text, ?array $buttons = null, bool $isTrash = true): void
    {
        $message = $this->messageSender->createMessage($text, $buttons);
        $this->senderService->sendPhoto($message, $imageUrl, $isTrash);
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
        $message = $this->messageSender->createMessage($text, $buttons);
        $this->senderService->sendMessage($message, $isTrash);
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

        $this->senderService->sendPoll($poll, $isTrash);
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

        if ($aiRequest->count()) {
            return $this->senderService->isMembership();
        }

        return true;
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

        $this->sendPhoto(
            imageUrl: asset('assets/img/start.png'),
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

        $this->sendMessage(
            text: self::HELP_TEXT,
            buttons: [new ButtonDto(CommandEnum::START->value, 'Назад')]
        );
    }

    /**
     * If user pressed "/account" button
     *
     * @return void
     */
    public function account(): void
    {
        $this->addToTrash();

        $buttons = [
            new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRED_USERS->value, 'Приглашенные пользователи'),
            new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRAL_LINK->value, 'Моя реферальная ссылка'),
            new ButtonDto(CommandEnum::START->value, 'Назад'),
        ];

        $this->sendMessage(
            text: self::ACCOUNT_TEXT,
            buttons: $buttons
        );
    }

    /**
     * @return void
     */
    public function showReferralLink(): void
    {
        $user = User::getOrCreate($this->repository);
        $referrerLink = config('services.telegram.botLink') . '?start=' . $user->referrer_link;

        $text = "🎓 Создавай тесты, играй в квиз с друзьями не выходя из телеграмма. ";
        $text .= "Участвуй в акциях и выигрывай ценные призы!\n\n";
        $text .= "🎲 Присоединяйся сейчас\n\n{$referrerLink}";

        $this->sendPhoto(
            imageUrl: asset('assets/img/referral.png'),
            text: $text,
            buttons: [new ButtonDto(CommandEnum::ACCOUNT->value, 'Назад')]
        );
    }

    /**
     * @return void
     */
    public function showReferredUsers(): void
    {
        $text = 'У вас пока нет приглашенных пользователей.';

        $user = User::getOrCreate($this->repository);
        $referredUsers = $user->referredUsers;
        if ($referredUsers->count()) {
            $text = "Ваши приглашенные пользователи:\n";
            foreach ($referredUsers as $referredUser) {
                $refUser = User::find($referredUser->referred_user_id);
                $text .= "\n<a href='https://t.me/{$refUser->username}'>{$refUser->username}</a>\n";
            }
        }

        $this->sendMessage(
            text: $text,
            buttons: [new ButtonDto(CommandEnum::ACCOUNT->value, 'Назад')]
        );
    }

    public function adminMenu(): void
    {
        $user = User::getOrCreate($this->repository);
        $buttons = [
            new ButtonDto(CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value, 'Создать рассылку'),
            new ButtonDto(CommandEnum::START->value, 'Вернуться в начало')
        ];

        if ($user->is_admin) {
            $this->sendMessage(
                text: 'Меню администратора:',
                buttons: $buttons
            );
            return;
        }

        $this->someProblemMessage();
    }

    public function adminNewsletterWaiting(): void
    {
        $user = User::getOrCreate($this->repository);
        if ($user->is_admin) {
            $newsletterWaitingState = State::where('code', StateConstants::NEWSLETTER_WAITING)->first();

            if ($user->hasAnyState())
                $user->states()->detach();

            if ($userFlow = $user->getOpenedFlow())
                $userFlow->delete();

            $user->states()->attach($newsletterWaitingState->id);

            $message = "Введите сообщение и прикрепите файлы (если необходимо) для рассылки пользователям:\n\n";
            $message .= "❗️После отправки сообщения отменить или удалить его будет невозможно!!!";

            $this->sendMessage(
                text: $message,
                buttons: [new ButtonDto(CommandEnum::ADMIN->value, 'Назад')]
            );

            return;
        }

        $this->someProblemMessage();
    }

    public function adminNewsletterConfirm(): void
    {
        $user = User::getOrCreate($this->repository);
        $newsletterWaitingState = State::where('code', StateConstants::NEWSLETTER_WAITING)->first();
        if (
            $user->is_admin
            && ($newsletterWaitingState && $user->states->contains($newsletterWaitingState->id))
        ) {
            if ($user->hasAnyState())
                $user->states()->detach();

            $this->sendMessage(
                text: 'Проверка сообщения...',
                buttons: [
                    new ButtonDto('accept', 'Все верно, разослать сообщения'),
                    new ButtonDto('cancel', 'Отмена'),
                ]
            );
            return;
        }

        $this->someProblemMessage();
    }

    /**
     * If user pressed to "support" button
     *
     * @return void
     */
    public function support(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: self::SUPPORT_TEXT,
            buttons: [new ButtonDto(CommandEnum::START->value, 'Назад')]
        );
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

        if ($openAiCompletion === null) {
            $this->someProblemMessage();
            return;
        }

        $flow = $user->getOpenedFlow();
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
                callbackData: CommandEnum::START->value,
                text: 'Выбрать другую тему'
            ),
            new ButtonDto(
                callbackData: SurveyCallbackEnum::REPEAT_FLOW->value,
                text: 'Создать еще 5 вопросов'
            )
        ];

        $this->sendMessage($message, $buttons);
    }

    public function someProblemMessage(): void
    {
        $this->sendMessage(
            'Что-то пошло не так. Попробуйте еще раз',
            [new ButtonDto(CommandEnum::START->value, 'Начать сначала')]
        );
    }

    public function subscribeToCommunity(): void
    {
        $message = "Подпишись на <a href='https://t.me/corgish_ru'>наш канал</a>, чтобы продолжить...";
        $this->sendMessage(
            $message,
            [new ButtonDto(CommandEnum::START->value, 'Я подписался')]
        );
    }
}
