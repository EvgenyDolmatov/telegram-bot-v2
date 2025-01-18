<?php

namespace App\Services;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Enums\ThemeEnum;
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

        $hasCorrectAnswer = 'верных ответов может быть несколько';
        if ($data[StateEnum::PollTypeChoice->value] === CallbackEnum::TypeQuiz->value) {
            $template['question1']['correct_answer'] = 'c';
            $template['question2']['correct_answer'] = 'a';

            $hasCorrectAnswer = 'один из которых верный';
        }

        $body = [
            'model' => config('services.openai.model'),
            'response_format' => [
                'type' => 'json_object'
            ],
            'messages' => [
//                [
//                    'role' => 'system',
//                    'content' => "Ты преподаватель в сфере $sector. Тебе нужно сгенерировать 5 вопросов $difficulty сложности, состоящие из 4 вариантов ответов$hasCorrectAnswer. Вопрос должен быть понятным и емким,
//                    но не превышать 255 символов. Ответы должны быть понятны отвечающим, но не превышать 100 символов. Ответ пришли в формате JSON. Пример JSON ответа: " . json_encode($template)
//                ]
                [
                    'role' => 'system',
                    'content' => "Ты — мастер по созданию квизов (викторин). Ты умеешь создавать тесты с 4-мя " .
                                 "вариантами ответов, $hasCorrectAnswer. Тебе нужно составить тест из 5 вопросов. В " .
                                 "каждом вопросе должно быть 4 варианта ответа, один из которых верный. Вопросы и " .
                                 "ответы, должны соответствовать интернет поиску. Ответ пришли в формате JSON. Пример" .
                                 " JSON ответа: " . json_encode($template)
                ]
            ]
        ];

        // If subject choice exists
        if (isset($data[StateEnum::PollThemeChoice->value])) {
            $themeCode = $data[StateEnum::PollThemeChoice->value] ?? null;
            $body['messages'][] = [
                'role' => 'user',
                'content' => "Тема: " . ThemeEnum::tryFrom($themeCode)->getName()
            ];
        }

        // If theme request exists
        if (isset($data[StateEnum::PollRequestWaiting->value])) {
            $body['messages'][] = [
                'role' => 'user',
                'content' => $data[StateEnum::PollRequestWaiting->value]
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
