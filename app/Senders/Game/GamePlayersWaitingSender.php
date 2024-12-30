<?php

namespace App\Senders\Game;

use App\Builder\Poll\PollBuilder;
use App\Dto\Telegram\CommunityDto;
use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Dto\Telegram\MessageDto;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Exceptions\ChatNotFoundException;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Poll;
use App\Models\User;
use App\Repositories\Telegram\Response\CommunityRepository;
use App\Senders\AbstractSender;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class GamePlayersWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GamePlayersWaiting;

    public function send(): void
    {
        $this->addToTrash();

        $organizer = $this->user;

        if ($this->getInputText() === CallbackEnum::GameJoinUserToQuiz->value) {
            $organizer = $this->getOrganizer();
            $chatId = $this->getCommunityDto($organizer)->getId();

            $game = $this->getGame($organizer);
            $messageId = $game->message_id;

            $text = "{$game->title}\n{$game->description}\n";

            // 1. Создать пользователя
            $user = User::getOrCreate($this->repository, 'player');

            $player = $game->players->where('user_id', $user->id)->first();

            if (!$player) {
                GamePlayer::create([
                    'game_id' => $game->id,
                    'user_id' => $user->id
                ]);
            }

            $allPlayers = GamePlayer::where('game_id', $game->id)->get();
            $userIds = array_map(fn ($player) => $player['user_id'], $allPlayers->toArray());
            $users = User::whereIn('id', $userIds)->get();

            $usernames = [];
            foreach ($users as $user) {
                $usernames[] = $this->getUsername($user);
            }

            $text .= "Время на ответ: {$game->time_limit} секунд\n\nУчастники: \n" . implode("\n", $usernames);

            $buttons = [new ButtonDto(
                callbackData: CallbackEnum::GameJoinUserToQuiz->value,
                text: CallbackEnum::GameJoinUserToQuiz->buttonText())
            ];

            $this->editMessage(
                messageId: $messageId,
                text: $text,
                buttons: $buttons,
                chatId: $chatId
            );
            return;
        }

        // Send message as private to organizer
        $this->sendMessageToPrivate();

        // Send message to community
        $this->sendMessageToCommunity($organizer);
    }

    private function getUsername(User $user): string
    {
        if ($user->username) {
            Log::debug('USERNAME: ' . $user->username);
            return $user->username;
        }

        if ($user->first_name || $user->last_name) {
            Log::debug('USERNAME FL: ' . "{$user->first_name} {$user->last_name}");
            return "{$user->first_name} {$user->last_name}";
        }

        return 'Anonymous user';
    }

    private function sendMessageToPrivate(): void
    {
        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }

    /**
     * @throws ChatNotFoundException
     */
    private function sendMessageToCommunity(User $organizer): void
    {
        $chatId = $this->getCommunityDto($organizer)->getId();
        $game = $this->getGame($organizer);

        $text = "{$game->title}\n";
        $text .= "{$game->description}\n";
        $text .= "Время на ответ: {$game->time_limit} секунд\n\n";

        $buttons = [new ButtonDto(
            callbackData: CallbackEnum::GameJoinUserToQuiz->value,
            text: CallbackEnum::GameJoinUserToQuiz->buttonText())
        ];

        $response = $this->sendMessage(
            text: $text,
            buttons: $buttons,
            chatId: $chatId
        );

        $game->update(['message_id' => $response['result']['message_id'] ?? null]);
    }

//    private function sendPoll(): void
//    {
//        $optionLetters = ['a','b','c','d'];
//
//        $chatId = $this->getCommunityDto()->getId();
//        $game = $this->getGame();
//        $pollIds = explode(',', $game->poll_ids);
//
//        $poll = Poll::where('tg_message_id', $pollIds[0])->first();
//
//        if ($poll) {
//            $options = array_map(fn ($option) => $option['text'], $poll->options->toArray());
//
//            $correctOptionId = $poll->correct_option_id
//                ? $optionLetters[$poll->correct_option_id]
//                : null;
//
//            $pollBuilder = $this->pollBuilder
//                ->setBuilder(new PollBuilder())
//                ->createPoll(
//                    question: $poll->question,
//                    options: $options,
//                    isAnonymous: $poll->is_anonymous,
//                    isQuiz: !$poll->allows_multiple_answers,
//                    correctOptionId: $correctOptionId,
//                );
//
//            $this->senderService->sendPoll($pollBuilder, $chatId);
//        }
//    }

    /**
     * @throws ChatNotFoundException
     */
    private function getCommunityDto(User $organizer): CommunityDto
    {
        $channelName = $this->getGame($organizer)->channel;

        if (!$channelName) {
            throw new ChatNotFoundException("Channel does not exist.");
        }

        try {
            $response = $this->senderService->getChatByChannelName($channelName);
            $channelDto = (new CommunityRepository(json_decode($response, true)))->createDto();
        } catch (Throwable $e) {
            throw new ChatNotFoundException("Wrong channel $channelName");
        }

        return $channelDto;
    }

    /**
     * @throws Exception
     */
    private function getGame(User $organizer): Game
    {
        try {
            $game = $organizer->games->last();
        } catch (Exception $e) {
            Log::error('Game does not exists for current user.', ['message' => $e->getMessage()]);
            throw new Exception('Game does not exists for current user.', 400);
        }

        return $game;
    }

    private function getOrganizer(): User
    {
        $messageDto = $this->getMessageDto();
        $channelName = '@' . $messageDto->getChat()->getUsername();
        $messageId = $messageDto->getId();

        $game = Game::where('channel', $channelName)->where('message_id', $messageId)->first();

        return $game->organizer;
    }
}
