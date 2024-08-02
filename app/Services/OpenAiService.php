<?php

namespace App\Services;

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

    public function __construct()
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
                    'content' => "Ты специалист в сфере IT. Тебе нужно сгенерировать 5 сложных вопросов, состоящие из 4 вариантов ответов, с одним правильным ответом. Вопрос должен быть понятным и емким,
                    но не превышать 255 символов. Ответы должны быть понятны отвечающим, но не превышать 100 символов. Ответ пришли в формате JSON. Пример JSON ответа: ".$template
                ],
                [
                    'role' => 'user',
                    'content' => "Предмет: WEB-разработка"
                ],
                [
                    'role' => 'user',
                    'content' => "Тема: Верстка HTML страниц"
                ]
            ]
        ];

        return Http::withHeaders($this->headers)
            ->withOptions(['proxy' => $this->proxy])
            ->post($url, $body);
    }
}
