<?php

namespace App\Services;

use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Models\Sector;
use App\Models\Subject;
use App\Models\User;
use App\Services\Traits\Proxy;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiService
{
    use Proxy;

    private string $proxy = 'http://' . self::PROXY_LOGIN . ':' . self::PROXY_PASSWORD . '@' . self::PROXY_HOST;
    private string $token;
    private array $headers;

    public function __construct(private readonly User $user)
    {
        $this->token = config('services.openai.token');
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function getModels(): void
    {
        $url = 'https://api.openai.com/v1/models';

        $response = Http::withHeaders($this->headers)
            ->withOptions(['proxy' => $this->proxy])
            ->get($url);

        Log::debug('Open AI: ' . $response);
    }

    /**
     * @return Response
     */
    public function getCompletions(): Response
    {
        $user = $this->user;
        $data = $user->getFlowData();

        Log::debug('Open AI: ' . json_encode($data));

        $url = 'https://api.openai.com/v1/chat/completions';
        $template = [
            'question1' => [
                'question_text' => 'Что обозначает аббревиатура HTML?',
                'options' => [
                    'a' => 'Hyper Text Markup Language',
                    'b' => 'Hyperlinks and Text Markup Language',
                    'c' => 'High Traffic Management Language',
                    'd' => 'High Traffic Language',
                ]
            ],
            'question2' => [
                'question_text' => 'Какой язык программирования чаще всего используется для создания динамических веб-сайтов?',
                'options' => [
                    'a' => 'Python',
                    'b' => 'JavaScript',
                    'c' => 'C++',
                    'd' => 'C#',
                ]
            ],
        ];

        $hasCorrectAnswer = '';

        Log::debug("OpenAI: " . $data[StateEnum::POLL_TYPE_CHOICE->value]);
        Log::debug("OpenAI: " . PollEnum::TYPE_QUIZ->value);

        if ($data[StateEnum::POLL_TYPE_CHOICE->value] === PollEnum::TYPE_QUIZ->value) {
            $template['question1']['correct_answer'] = 'c';
            $template['question2']['correct_answer'] = 'a';

            $hasCorrectAnswer = ', с одним правильным ответом';
        }

        $difficultyData = [
            PollEnum::LEVEL_EASY->value => 'низкой',
            PollEnum::LEVEL_MIDDLE->value => 'средней',
            PollEnum::LEVEL_HARD->value => 'высокой',
            PollEnum::LEVEL_ANY->value => 'любой',
        ];

        $sector = Sector::where('code', $data[StateEnum::POLL_SECTOR_CHOICE->value])->first()->title;
        $difficulty = $difficultyData[$data[StateEnum::POLL_DIFFICULTY_CHOICE->value]];

        $body = [
            'model' => config('services.openai.model'),
            'response_format' => [
                'type' => 'json_object'
            ],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Ты преподаватель в сфере $sector. Тебе нужно сгенерировать 5 вопросов $difficulty сложности, состоящие из 4 вариантов ответов$hasCorrectAnswer. Вопрос должен быть понятным и емким,
                    но не превышать 255 символов. Ответы должны быть понятны отвечающим, но не превышать 100 символов. Ответ пришли в формате JSON. Пример JSON ответа: " . json_encode($template)
                ]
            ]
        ];

        // If subject choice exists
        if (isset($data[StateEnum::POLL_SUBJECT_CHOICE->value])) {
            $subject = Subject::where('code', $data[StateEnum::POLL_SUBJECT_CHOICE->value])->first()->title;
            $body['messages'][] = [
                'role' => 'user',
                'content' => "Предмет: $subject"
            ];
        }

        // If theme request exists
        if (isset($data[StateEnum::POLL_THEME_WAITING->value])) {
            $theme = $data[StateEnum::POLL_THEME_WAITING->value];
            $body['messages'][] = [
                'role' => 'user',
                'content' => "Тема: $theme"
            ];
        }

        try {
            $response = Http::withHeaders($this->headers)
                ->withOptions(['proxy' => $this->proxy])
                ->post($url, $body);
        } catch (Throwable $throwable) {
            Log::error('Can\'t send request to OpenAI API. Maybe some problems with token.', [
                'message' => $throwable->getMessage()
            ]);
        }

        return $response;
    }
}
