<?php

namespace App\Constants;

interface ButtonConstants
{
    public const array SUPPORT = [
        ButtonKeyConstants::TEXT => 'Поддержка',
        ButtonKeyConstants::CALLBACK => 'support'
    ];

    public const array CREATE_SURVEY = [
        ButtonKeyConstants::TEXT => 'Создать тест',
        ButtonKeyConstants::CALLBACK => 'create_survey'
    ];
}
