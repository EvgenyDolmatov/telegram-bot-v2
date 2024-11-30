<?php

namespace App\Enums;

enum PollEnum: string
{
    case CREATE_SURVEY = 'create_survey';
    case TYPE_QUIZ = 'type_quiz';
    case TYPE_SURVEY = 'type_survey';
    case IS_ANON = 'is_anon';
    case IS_NOT_ANON = 'is_not_anon';
    case LEVEL_EASY = 'level_easy';
    case LEVEL_MIDDLE = 'level_middle';
    case LEVEL_HARD = 'level_hard';
    case LEVEL_ANY = 'level_any';
    case REPEAT_FLOW = 'repeat_flow';
    case SEND_TO_CHANNEL = 'send_to_channel';
    case ACCEPT_POLLS = 'accept_polls_and_send_to_channel';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::CREATE_SURVEY => StateEnum::POLL_TYPE_CHOICE,
            self::TYPE_QUIZ,
            self::TYPE_SURVEY => StateEnum::POLL_ANONYMITY_CHOICE,
            self::IS_ANON,
            self::IS_NOT_ANON => StateEnum::POLL_DIFFICULTY_CHOICE,
            self::LEVEL_HARD,
            self::LEVEL_MIDDLE,
            self::LEVEL_EASY,
            self::LEVEL_ANY => StateEnum::POLL_SECTOR_CHOICE,
            self::SEND_TO_CHANNEL => StateEnum::CHANNEL_POLLS_CHOICE,
            self::ACCEPT_POLLS => StateEnum::CHANNEL_NAME_WAITING,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            self::CREATE_SURVEY => "Создать тест",
            self::TYPE_QUIZ => "Викторина (1 вариант ответа)",
            self::TYPE_SURVEY => "Опрос (несколько вариантов)",
            self::IS_ANON => "Да",
            self::IS_NOT_ANON => "Нет",
            self::LEVEL_HARD => "Высокая сложность",
            self::LEVEL_MIDDLE => "Средняя сложность",
            self::LEVEL_EASY => "Низкая сложность",
            self::LEVEL_ANY => "Любая сложность",
            self::REPEAT_FLOW => "Создать еще 5 вопросов",
            self::SEND_TO_CHANNEL => "Отправить в сообщество/канал",
            self::ACCEPT_POLLS => "Отправить выбранные вопросы",
        };
    }
}
