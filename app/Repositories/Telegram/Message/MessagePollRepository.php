<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\Message\Component\PollDto;
use App\Dto\Telegram\Message\MessagePollDto;
use App\Dto\Telegram\Message\Poll\OptionDto;
use App\Repositories\Telegram\Request\MessageRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class MessagePollRepository extends MessageRepository
{
    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessagePollDto
    {
        $data = $data ?? $this->payload['result'];

        try {
            $dto = (new MessagePollDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setPoll($this->getPollDto($data['poll']));
        } catch (Exception $e) {
            Log::error('Some problem occurred with MessageTextRepository.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessageTextRepository.');
        }

        return $dto;
    }

    /**
     * @throws Exception
     */
    private function getPollDto(array $data): PollDto
    {
        try {
            $dto = (new PollDto())
                ->setId($data['id'])
                ->setQuestion($data['question'])
                ->setOptions($this->getOptions($data['options']))
                ->setTotalVoterCount($data['total_voter_count'])
                ->setIsClosed($data['is_closed'])
                ->setIsAnonymous($data['is_anonymous'])
                ->setType($data['type'])
                ->setIsAllowsMultipleAnswers($data['allows_multiple_answers'])
                ->setCorrectOptionId($data['correct_option_id'] ?? null);
        } catch (Exception $e) {
            Log::error('Some problem occurred with getting poll.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessageTextRepository.');
        }

        return $dto;
    }

    /**
     * @param array $optionsList
     * @return OptionDto[]
     */
    private function getOptions(array $optionsList): array
    {
        $options = [];

        foreach ($optionsList as $option) {
            $options[] = (new OptionDto())
                ->setText($option['text'])
                ->setVoterCount($option['voter_count']);
        }

        return $options;
    }
}
