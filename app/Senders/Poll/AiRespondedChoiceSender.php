<?php

namespace App\Senders\Poll;

use App\Builder\Poll\PollBuilder;
use App\Dto\OpenAiCompletionDto;
use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PreparedPoll;
use App\Models\UserFlow;
use App\Repositories\OpenAiRepository;
use App\Repositories\PollRepository;
use App\Senders\AbstractSender;
use App\Services\OpenAiService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Logging\Exception;

class AiRespondedChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::POLL_AI_RESPONDED_CHOICE;

    public function send(): void
    {
        // Send message about waiting...
        $this->editMessageCaption(
            messageId: $this->user->tg_message_id,
            text: 'Подождите. Ваш запрос обрабатывается...'
        );

        // Get response dto from open ai
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
        $this->sendPhoto(
            imageUrl: asset('assets/img/start.png'),
            text: self::STATE->title(),
            buttons: self::STATE->buttons()
        );
    }

    private function getAiCompletionDto(): OpenAiCompletionDto
    {
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
        bool    $isAnonymous,
        bool    $isQuiz = false,
        ?string $correctOptionId = null,
        ?int    $chatId = null,
        bool    $isTrash = true
    ): Response {
        try {
            // Send poll message
            $pollBuilder = $this->pollBuilder
                ->setBuilder(new PollBuilder())
                ->createPoll($question, $options, $isAnonymous, $isQuiz, $correctOptionId);

            $response = $this->senderService->sendPoll($pollBuilder, $chatId, $isTrash);
        } catch (\Throwable $exception) {
            throw new Exception('An error occurred while submitting the poll');
        }

        try {
            $messageId = $response['result']['message_id'] ?? null;
            $pollDto = (new PollRepository($response))->getDto();

            // Save poll to database
            $poll = Poll::create([
                'user_id' => $this->user->id,
                'tg_message_id' => $messageId,
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

            foreach ($questions as $question) {
                $response = $this->sendPoll(
                    question: $question->getText(),
                    options: $question->getOptions(),
                    isAnonymous: $flow->isAnonymous(),
                    isQuiz: $flow->isQuiz(),
                    correctOptionId: $question->getAnswer(),
                    isTrash: false
                );

                // Prepare message with correct answers
                if ($flow->isQuiz()) {
                    $questionNumber++;
                    $questionText = trim($question->getText(), ':');
                    $correctAnswers .= "\n\nВопрос № $questionNumber. [ID: {$response['result']['message_id']}] $questionText";
                    $correctAnswers .= "\nПравильный ответ: {$question->getOptions()[$question->getAnswer()]}";
                }
            }

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
}
