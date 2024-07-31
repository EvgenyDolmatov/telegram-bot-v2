<?php

namespace App\Constants;

interface ButtonConstants
{
    public const array SUPPORT = [
        ButtonKeyConstants::TEXT => 'Поддержка',
        ButtonKeyConstants::CALLBACK => CallbackConstants::SUPPORT
    ];

    public const array CREATE_SURVEY = [
        ButtonKeyConstants::TEXT => 'Создать тест',
        ButtonKeyConstants::CALLBACK => CallbackConstants::CREATE_SURVEY
    ];

    public const array QUIZ = [
        ButtonKeyConstants::TEXT => 'Викторина (1 вариант ответа)',
        ButtonKeyConstants::CALLBACK => CallbackConstants::TYPE_QUIZ
    ];

    public const array SURVEY = [
        ButtonKeyConstants::TEXT => 'Опрос (несколько вариантов)',
        ButtonKeyConstants::CALLBACK => CallbackConstants::TYPE_SURVEY
    ];

    public const array IS_ANON = [
        ButtonKeyConstants::TEXT => 'Да',
        ButtonKeyConstants::CALLBACK => CallbackConstants::IS_ANON
    ];

    public const array IS_NOT_ANON = [
        ButtonKeyConstants::TEXT => 'Нет',
        ButtonKeyConstants::CALLBACK => CallbackConstants::IS_NOT_ANON
    ];

    public const array LEVEL_EASY = [
        ButtonKeyConstants::TEXT => 'Низкая сложность',
        ButtonKeyConstants::CALLBACK => CallbackConstants::LEVEL_EASY
    ];

    public const array LEVEL_MIDDLE = [
        ButtonKeyConstants::TEXT => 'Средняя сложность',
        ButtonKeyConstants::CALLBACK => CallbackConstants::LEVEL_MIDDLE
    ];

    public const array LEVEL_HARD = [
        ButtonKeyConstants::TEXT => 'Высокая сложность',
        ButtonKeyConstants::CALLBACK => CallbackConstants::LEVEL_HARD
    ];
}
