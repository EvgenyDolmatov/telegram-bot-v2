<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Builder\Poll\PollBuilder;
use App\Builder\PollSender;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Enums\SurveyCallbackEnum;
use App\Models\AiRequest;
use App\Models\Newsletter;
use App\Models\State;
use App\Models\Subject;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\ChannelRepository;
use App\Repositories\OpenAiRepository;
use App\Repositories\PollRepository;
use App\Repositories\RequestRepository;
use App\Services\OpenAiService;
use App\Services\SenderService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * @param null $chatId
     * @return void
     */
    public function sendPhoto(string $imageUrl, string $text, ?array $buttons = null, bool $isTrash = true, $chatId = null): void
    {
        $message = $this->messageSender->createMessage($text, $buttons);
        $this->senderService->sendPhoto($message, $imageUrl, $isTrash, $chatId);
    }

    /**
     * Send simple message or message with buttons
     *
     * @param string $text
     * @param array|null $buttons
     * @param bool $isTrash
     * @return void
     * @throws \Exception
     */
    public function sendMessage(string $text, ?array $buttons = null, bool $isTrash = true, $chatId = null): void
    {
        $message = $this->messageSender->createMessage($text, $buttons);
        $this->senderService->sendMessage($message, $isTrash, $chatId);
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
     * @return Response
     */
    public function sendPoll(
        string  $question,
        array   $options,
        bool    $isAnonymous,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        bool    $isTrash = true
    ): Response
    {
        $poll = $this->pollSender
            ->setBuilder(new PollBuilder())
            ->createPoll($question, $options, $isAnonymous, $isQuiz, $correctOptionId);

        return $this->senderService->sendPoll($poll, $isTrash);
    }

    /**
     * @throws \Exception
     */
    public function addToTrash(): void
    {
        $messageDto = $this->repository->getDto();

        TrashMessage::add($messageDto->getChat(), $messageDto, true);
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

    /**
     * Send message to channel
     * Example: /channel @evd_test_channel {534523,123213}
     */
    public function sendToChannel(array $messageData): void
    {
        $channelName = $messageData['parameter'] ?? "@" . ltrim($messageData['parameter'], '@');
        $pollIds = $messageData['arguments'] ?
            explode(',', trim($messageData['arguments'], '{}')) :
            null;

        Log::debug('PollIDs: ' . json_encode($pollIds));

        foreach ($pollIds as $pollId) {
            $channelResponse = $this->senderService->getChatByChannelName($channelName);
            $channelDto = (new ChannelRepository($channelResponse))->getDto();

            $this->sendMessage(
                text: 'ID: ' . $pollId,
                isTrash: false,
                chatId: $channelDto->getId()
            );
        }
    }

    /**
     * Admin menu
     */
    public function adminMenu(): void
    {
        $user = User::getOrCreate($this->repository);
        $buttons = [
            new ButtonDto(CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value, 'Создать рассылку'),
            new ButtonDto(CommonCallbackEnum::ADMIN_STATISTIC_MENU->value, 'Статистика бота'),
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

    /**
     * Waiting newsletter content (text and photo) from admin
     */
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

    /**
     * Check newsletter content before sending
     * @throws \Exception
     */
    public function adminNewsletterConfirmation(): void
    {
        $user = User::getOrCreate($this->repository);
        $messageDto = $this->repository->getDto();
        $newsletterWaitingState = State::where('code', StateConstants::NEWSLETTER_WAITING)->first();

        if (
            $user->is_admin
            && ($newsletterWaitingState && $user->states->contains($newsletterWaitingState->id))
        ) {
            if ($user->hasAnyState())
                $user->states()->detach();

            $images = $messageDto->getPhoto();

            $photoId = (end($images))->getFileId();

            $photoPath = $this->senderService->uploadPhoto($photoId);
            $newsletterData = [
                'user_id' => $user->id,
                'text' => $messageDto->getText()
            ];

            if ($photoPath) {
                $newsletterData['image'] = 'uploads/' . $photoPath;
            }

            $newsletter = Newsletter::create($newsletterData);

            $this->sendMessage(
                text: "Внимательно проверьте Ваше сообщение!!! \n\nПосле подтверждения, это сообщение отправится всем подписчикам бота.",
                isTrash: false
            );

            if ($photoPath) {
                $this->sendPhoto(
                    imageUrl: asset($newsletter->image),
                    text: $newsletter->text,
                    buttons: [
                        new ButtonDto(
                            CommonCallbackEnum::ADMIN_CONFIRM_NEWSLETTER->value,
                            'Все верно, отправить сообщение всем участникам!'
                        ),
                        new ButtonDto(
                            CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value,
                            'Загрузить другое сообщение'
                        )
                    ]
                );
                return;
            }

            $this->sendMessage(
                text: $newsletter->text,
                buttons: [
                    new ButtonDto(
                        CommonCallbackEnum::ADMIN_CONFIRM_NEWSLETTER->value,
                        'Все верно, отправить сообщение всем участникам!'
                    ),
                    new ButtonDto(
                        CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value,
                        'Загрузить другое сообщение'
                    )
                ]
            );
            return;
        }

        $this->someProblemMessage();
    }

    /**
     * Newsletter successful sent
     */
    public function adminNewsletterSent(): void
    {
        $allUsers = User::all();
        $currentUser = User::getOrCreate($this->repository);
        $lastNewsletter = $currentUser->newsletters->last();

        $chatIds = [];
        foreach ($allUsers as $user) {
            $chatIds[] = $user->tg_chat_id;
        }

        if ($lastNewsletter->image) {
            foreach ($chatIds as $chatId) {
                $this->sendPhoto(
                    imageUrl: asset($lastNewsletter->image),
                    text: $lastNewsletter->text,
                    isTrash: false,
                    chatId: $chatId
                );
            }
        } else {
            foreach ($chatIds as $chatId) {
                $this->sendMessage(
                    text: $lastNewsletter->text,
                    isTrash: false,
                    chatId: $chatId
                );
            }
        }
    }

    public function adminStatisticMenu(): void
    {
        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES->value,
                'Статистика тестов'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_USERS->value,
                'Статистика пользователей'
            ),
        ];

        $this->sendMessage(
            text: 'Статистика бота:',
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzes(): void
    {
        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_DAY->value,
                'За сегодня'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_WEEK->value,
                'За неделю'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_MONTH->value,
                'За месяц'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_QUARTER->value,
                'За квартал'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_YEAR->value,
                'За год'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: 'Статистика созданных тестов:',
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzesPerDay(): void
    {
        $requestsToday = AiRequest::whereDate('created_at', Carbon::today())->get();

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $text = "Сегодня тесты еще не создавались.";
        if ($requestsToday->count() > 0) {
            $text = "Количество созданных тестов за сегодня: {$requestsToday->count()}";
        }

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzesPerWeek(): void
    {
        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 week');
        $requestsWeek = AiRequest::whereBetween('created_at', [$startDate, $now])->get();

        $text = "За последнюю неделю не было создано ни одного теста.";
        if ($requestsWeek->count() > 0) {
            $text = "Количество созданных тестов за последнюю неделю: {$requestsWeek->count()}";
        }

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzesPerMonth(): void
    {
        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 month');
        $requestsMonth = AiRequest::whereBetween('created_at', [$startDate, $now])->get();

        $text = "За последний месяц не было создано ни одного теста.";
        if ($requestsMonth->count() > 0) {
            $text = "Количество созданных тестов за последний месяц: {$requestsMonth->count()}";
        }

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzesPerQuarter(): void
    {
        $now = Carbon::now();
        $startDate = $now->copy()->modify('-3 month');
        $requestsQuarter = AiRequest::whereBetween('created_at', [$startDate, $now])->get();

        $text = "За последний квартал не было создано ни одного теста.";
        if ($requestsQuarter->count() > 0) {
            $text = "Количество созданных тестов за последний квартал: {$requestsQuarter->count()}";
        }

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    public function adminStatisticQuizzesPerYear(): void
    {
        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 year');
        $requestsQuarter = AiRequest::whereBetween('created_at', [$startDate, $now])->get();

        $text = "За последний год не было создано ни одного теста.";
        if ($requestsQuarter->count() > 0) {
            $text = "Количество созданных тестов за последний год: {$requestsQuarter->count()}";
        }

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    public function adminStatisticUsers(): void
    {
        $usersCount = User::all()->count();

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_USERS_DAY->value,
                'Новые пользователи сегодня'
            ),
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_MENU->value,
                'Вернуться назад'
            ),
        ];

        $this->sendMessage(
            text: "Статистика пользователей:\n\nОбщее количество пользователей: {$usersCount}",
            buttons: $buttons
        );
    }

    public function adminStatisticUsersPerDay(): void
    {
        $usersToday = User::whereDate('created_at', Carbon::today())->get();

        $buttons = [
            new ButtonDto(
                CommonCallbackEnum::ADMIN_STATISTIC_USERS->value,
                'Вернуться назад'
            ),
        ];

        $text = "Новые пользователи сегодня не регистрировались.";
        if ($usersToday->count() > 0) {
            $text = "Количество зарегистрированных пользователей сегодня: {$usersToday->count()}";
        }

        $this->sendMessage(
            text: $text,
            buttons: $buttons
        );
    }

    /**
     * If user pressed to "support" button
     *
     * @return void
     * @throws \Exception
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

        try {
            $openAiCompletion = $openAiRepository->getCompletion();
        } catch (\Throwable $exception) {
            $this->someProblemMessage();
            Log::error("OpenAiCompletion error.", ['message' => $exception]);
            return;
        }

        $flow = $user->getOpenedFlow();
        if ($questions = $openAiCompletion->getQuestions()) {
            $correctAnswers = '';
            $questionNumber = 0;
            foreach ($questions as $question) {
                $pollResponse = $this->sendPoll(
                    question: $question->getText(),
                    options: $question->getOptions(),
                    isAnonymous: $flow->isAnonymous(),
                    isQuiz: $flow->isQuiz(),
                    correctOptionId: $question->getAnswer(),
                    isTrash: false
                );

                $pollDto = (new PollRepository($pollResponse))->getDto();

                if ($flow->isQuiz()) {
                    $questionNumber++;
                    $questionText = trim($question->getText(), ':');
                    $correctAnswers .= "\n\nВопрос № $questionNumber. [ID: {$pollDto->getId()}] $questionText";
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
