<?php

namespace App\Services;

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
use App\Models\Sector;
use App\Models\Subject;
use App\Models\User;
use App\Services\Traits\Proxy;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    public function getCompletions(): Response
    {
        $user = $this->user;
        $data = $user->getFlowData();

        $sector = Sector::where('code', $data[StateConstants::SECTOR_CHOICE])->first()->title;
        $subject = Subject::where('code', $data[StateConstants::SUBJECT_CHOICE])->first()->title;
        $theme = $data[StateConstants::THEME_REQUEST];

        $difficultyData = [
            CallbackConstants::LEVEL_EASY => 'легких',
            CallbackConstants::LEVEL_MIDDLE => 'средних',
            CallbackConstants::LEVEL_HARD => 'сложных',
        ];

        $difficulty = $difficultyData[$data[StateConstants::DIFFICULTY_CHOICE]];

        $url = 'https://api.openai.com/v1/chat/completions';
        $template = json_encode([
            'question1' => [
                'question_text' => 'Что обозначает аббревиатура HTML?',
                'options' => [
                    'a' => 'Hyper Text Markup Language',
                    'b' => 'Hyperlinks and Text Markup Language',
                    'c' => 'High Traffic Management Language',
                ],
                'correct_answer' => 'a'
            ],
            'question2' => [
                'question_text' => 'Какой язык программирования чаще всего используется для создания динамических веб-сайтов?',
                'options' => [
                    'a' => 'Python',
                    'b' => 'JavaScript',
                    'c' => 'C++',
                ],
                'correct_answer' => 'b'
            ],
        ]);

        $body = [
            'model' => 'gpt-3.5-turbo-0125',
            'response_format' => [
                'type' => 'json_object'
            ],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Ты специалист в сфере $sector. Тебе нужно сгенерировать 5 $difficulty вопросов по сложности, состоящие из 4 вариантов ответов, с одним правильным ответом. Вопрос должен быть понятным и емким,
                    но не превышать 255 символов. Ответы должны быть понятны отвечающим, но не превышать 100 символов. Ответ пришли в формате JSON. Пример JSON ответа: " . $template
                ],
                [
                    'role' => 'user',
                    'content' => "Предмет: $subject"
                ],
                [
                    'role' => 'user',
                    'content' => "Тема: $theme"
                ]
            ]
        ];

        return Http::withHeaders($this->headers)
            ->withOptions(['proxy' => $this->proxy])
            ->post($url, $body);
    }
}
