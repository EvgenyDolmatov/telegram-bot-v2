<?php

namespace App\Enums;

enum SurveyCallbackEnum: string
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
}
