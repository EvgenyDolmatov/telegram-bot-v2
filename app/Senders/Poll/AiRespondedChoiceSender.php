<?php

namespace App\Senders\Poll;

use App\Builder\Poll\PollBuilder;
use App\Dto\OpenAi\OpenAiQuestionDto;
use App\Dto\OpenAi\OpenAiUsageDto;
use App\Dto\OpenAiCompletionDto;
use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Models\Poll;
use App\Models\PollGroup;
use App\Models\PollOption;
use App\Models\PreparedPoll;
use App\Models\UserFlow;
use App\Repositories\OpenAiRepository;
use App\Repositories\Telegram\Message\MessagePollRepository;
use App\Senders\AbstractSender;
use App\Services\OpenAiService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Logging\Exception;

class AiRespondedChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        // Send message about waiting...
        $this->sendMessage('Подождите. Ваш запрос обрабатывается...');

        if (!$aiCompletionDto = $this->getAiCompletionDto()) {
            $this->someProblemMessage();
            return;
        }

        // Send polls
        $this->sendPolls($aiCompletionDto);

        // Check if user is subscriber
        if (!$this->canContinue()) {
            $this->subscribeToCommunity();
            return;
        }

        // Send message after AI response
        $this->sendMessage(
            text: StateEnum::PollAiRespondedChoice->title(),
            buttons: StateEnum::PollAiRespondedChoice->buttons(),
            isTrash: false
        );
    }

    private function getAiCompletionDto(): OpenAiCompletionDto
    {
        if (env('OPEN_AI_TEST_MODE')) {
            return $this->getTestCompletionDto();
        }

        // Connect to OpenAI service
        $aiService = new OpenAiService($this->user);
        $aiRepository = new OpenAiRepository($aiService);
        $aiCompletionDto = null;

        try {
            $aiCompletionDto = $aiRepository->getCompletion();
        } catch (\Throwable $exception) {
            Log::error("Open AI completion has error", [
                'exception' => $exception
            ]);
        }

        return $aiCompletionDto;
    }

    private function sendPoll(
        string  $question,
        array   $options,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        ?int    $chatId = null,
        bool    $isTrash = true
    ): Response {
        try {
            // Send poll message
            $pollBuilder = $this->pollBuilder
                ->setBuilder(new PollBuilder())
                ->createPoll($question, $options, $isQuiz, $correctOptionId);

            $response = $this->senderService->sendPoll($pollBuilder, $chatId, $isTrash);
        } catch (\Throwable $exception) {
            throw new Exception('An error occurred while submitting the poll');
        }

        try {
            $pollData = json_decode($response, true);
            $messagePollDto = (new MessagePollRepository($pollData))->createDto();
            $pollDto = $messagePollDto->getPoll();

            // Save poll to database
            $poll = Poll::create([
                'user_id' => $this->user->id,
                'tg_message_id' => $messagePollDto->getId(),
                'question' => $pollDto->getQuestion(),
                'is_anonymous' => $pollDto->getIsAnonymous(),
                'allows_multiple_answers' => $pollDto->getIsAllowsMultipleAnswers(),
                'type' => $pollDto->getType(),
                'correct_option_id' => $pollDto->getCorrectOptionId(),
            ]);

            // Save poll options to database
            foreach ($pollDto->getOptions() as $option) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'text' => $option->getText()
                ]);
            }
        } catch (\Throwable $exception) {
            throw new Exception('Poll data was occurrence');
        }

        // Save prepared polls
        $this->savePreparedPollToDb($poll);

        return $response;
    }

    private function sendPolls(OpenAiCompletionDto $dto): void
    {
        $flow = $this->user->getOpenedFlow();

        if ($questions = $dto->getQuestions()) {
            $correctAnswers = '';
            $questionNumber = 0;

            $messagePollIds = [];

            foreach ($questions as $question) {
                $response = $this->sendPoll(
                    question: $question->getText(),
                    options: $question->getOptions(),
                    isQuiz: $flow->isQuiz(),
                    correctOptionId: $question->getAnswer(),
                    isTrash: false
                );

                $messageId = $response['result']['message_id'];

                // Prepare message with correct answers
                if ($flow->isQuiz()) {
                    $questionNumber++;
                    $questionText = trim($question->getText(), ':');
                    $correctAnswers .= "\n\nВопрос № $questionNumber. [ID: $messageId] $questionText";
                    $correctAnswers .= "\nПравильный ответ: {$question->getOptions()[$question->getAnswer()]}";
                }

                $messagePollIds[] = $messageId;
            }

            // Update poll group
            $this->updatePollGroup($messagePollIds);

            // Send message with correct answers
            if ($correctAnswers !== '') {
                $this->sendMessage(
                    text: $correctAnswers,
                    isTrash: false
                );
            }

            // Save AI response to database
            $this->saveAiRequestToDb($flow, $dto);

            // Close current flow
            $flow->update(['is_completed' => true]);
        }
    }

    private function updatePollGroup(array $pollIds): void
    {
        $pollGroup = PollGroup::where('user_id', $this->user->id)->where('is_closed', false)->first();

        if ($pollGroup) {
            $pollGroup->update([
                'poll_ids' => $pollGroup->poll_ids . ',' . implode(',', $pollIds),
            ]);
            return;
        }

        PollGroup::create([
            'user_id' => $this->user->id,
            'poll_ids' => implode(',', $pollIds),
        ]);
    }

    private function saveAiRequestToDb(UserFlow $flow, OpenAiCompletionDto $aiCompletionDto): void
    {
        AiRequest::create([
            'tg_chat_id' => $this->user->tg_chat_id,
            'user_flow_id' => $flow->id,
            'ai_survey' => json_encode(array_map(fn($question) => [
                'text' => $question->getText(),
                'options' => $question->getOptions(),
                'answer' => $question->getAnswer(),
            ], $aiCompletionDto->getQuestions())),
            'usage_prompt_tokens' => $aiCompletionDto->getUsage()->getPromptTokens(),
            'usage_completion_tokens' => $aiCompletionDto->getUsage()->getCompletionTokens(),
            'usage_total_tokens' => $aiCompletionDto->getUsage()->getTotalTokens(),
        ]);
    }

    private function savePreparedPollToDb(Poll $poll): void
    {
        $preparedPoll = $this->user->preparedPolls()->first();
        if ($preparedPoll) {
            $pollIds = explode(',', $preparedPoll->poll_ids);
            $checkedPollIds = explode(',', $preparedPoll->checked_poll_ids);

            $pollIds[] = $poll->tg_message_id;
            $checkedPollIds[] = $poll->tg_message_id;

            $preparedPoll->update([
                'tg_message_id' => $poll->tg_message_id,
                'poll_ids' => implode(',', $pollIds),
                'checked_poll_ids' => implode(',', $checkedPollIds),
            ]);
            return;
        }

        PreparedPoll::create([
            'user_id' => $this->user->id,
            'tg_message_id' => $poll->tg_message_id,
            'poll_ids' => $poll->tg_message_id,
            'checked_poll_ids' => $poll->tg_message_id,
        ]);
    }

    /**
     * Generate fake data for testing
     */
    private function getTestCompletionDto(): OpenAiCompletionDto
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = new OpenAiQuestionDto(
                text: "Вопрос $i?",
                options: [
                    "a" => "Ответ 1",
                    "b" => "Ответ 2",
                    "c" => "Ответ 3",
                    "d" => "Ответ 4",
                ],
                answer: "a"
            );
        }

        return new OpenAiCompletionDto(
            id: 'cmpl-uqkvlQyYK7bGYrRHQ0eXlWi7',
            object: 'chat.completion',
            createdAt: 1589478378,
            model: 'gpt-4-0125-preview',
            questions: $questions,
            usage: new OpenAiUsageDto(500, 500,1000)
        );
    }
}
