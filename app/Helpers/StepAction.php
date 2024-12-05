<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Constants\StateConstants;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Models\AiRequest;
use App\Models\Newsletter;
use App\Models\State;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StepAction
{
    private MessageSender $messageSender;
    private SenderService $senderService;
    private RequestRepository $repository;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
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
     * @param int|null $chatId
     * @return string
     * @throws \Exception
     */
    public function sendMessage(
        string $text,
        ?array $buttons = null,
        bool $isTrash = true,
        int $chatId = null
    ): string {
        $message = $this->messageSender->createMessage($text, $buttons);
        $response = $this->senderService->sendMessage($message, $isTrash, $chatId);

        return $response;
    }


    /**
     * @throws \Exception
     */
    public function addToTrash(): void
    {
        $requestDto = $this->repository->getDto();

        TrashMessage::add($requestDto->getChat()->getId(), $requestDto->getId(), true);
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

    public function someProblemMessage(): void
    {
        $this->sendMessage(
            'Что-то пошло не так. Попробуйте еще раз',
            [new ButtonDto(CommandEnum::START->value, 'Начать сначала')]
        );
    }
}
