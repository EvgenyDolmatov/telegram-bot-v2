<?php

namespace App\Enums\Callback;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\StateEnum;

enum GameEnum: string
{
    case Create = 'game_create';
    case PollsSave = 'game_polls_save';
    case TimeLimit15 = 'game_time_limit_15';
    case TimeLimit20 = 'game_time_limit_20';
    case TimeLimit25 = 'game_time_limit_25';
    case TimeLimit30 = 'game_time_limit_30';
    case TimeLimit45 = 'game_time_limit_45';
    case TimeLimit60 = 'game_time_limit_60';
    case TimeLimit180 = 'game_time_limit_180';
    case TimeLimit300 = 'game_time_limit_300';
    case TimeLimit600 = 'game_time_limit_600';
    case Edit = 'game_edit';
    case EditTitle = 'game_edit_title';
    case EditPolls = 'game_edit_polls';
    case EditTimeLimit = 'game_edit_time_limit';
    case TitleChange = 'game_title_change';
    case PollsChange = 'game_polls_change';
    case TimeLimitChange = 'game_time_limit_change';
    case AddToCommunity = 'game_add_to_community';
    case InvitationLink = 'game_invitation_link';
    case Start = 'game_start';
    case Statistics = 'game_statistics';


    public function toState(): StateEnum
    {
        return match ($this) {
            self::Create => StateEnum::GameTitleWaiting,
            self::PollsSave => StateEnum::GameTimeLimitChoice,
            self::TimeLimit15,
            self::TimeLimit20,
            self::TimeLimit25,
            self::TimeLimit30,
            self::TimeLimit45,
            self::TimeLimit60,
            self::TimeLimit180,
            self::TimeLimit300,
            self::TimeLimit600 => StateEnum::GameCreatedMenuShow,
            self::Edit => StateEnum::GameEditMenuShow,
            self::EditTitle => StateEnum::GameEditTitleWaiting,
            self::EditPolls => StateEnum::GameEditPollsChoice,
            self::EditTimeLimit,
            self::TitleChange,
            self::PollsChange,
            self::TimeLimitChange => StateEnum::GameEditTimeLimitChoice,
            self::Start => StateEnum::GameplayWaitingToStart,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            self::Create => "Создать игру из вопросов",
            self::PollsSave => "Отправить выбранные",
            self::TimeLimit15 => "15 секунд",
            self::TimeLimit20 => "20 секунд",
            self::TimeLimit25 => "25 секунд",
            self::TimeLimit30 => "30 секунд",
            self::TimeLimit45 => "45 секунд",
            self::TimeLimit60 => "1 минута",
            self::TimeLimit180 => "3 минуты",
            self::TimeLimit300 => "5 минут",
            self::TimeLimit600 => "10 минут",
            self::Edit => "Редактировать",
            self::EditTitle => "Редактировать название",
            self::EditPolls => "Редактировать вопросы",
            self::EditTimeLimit => "Редактировать время",
            self::PollsChange,
            self::TimeLimitChange => "Сохранить",

            self::AddToCommunity => "Добавить в группу",
            self::InvitationLink => "Пригласить игроков",
            self::Start => "Начать игру",
            self::Statistics => "Статистика",
        };
    }

    public function getButtonDto(?string $value = null, ?string $text = null): ButtonDto
    {
        return new ButtonDto(
            callbackData: $value ?? $this->value,
            text: $text ?? $this->buttonText()
        );
    }
}
