<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Senders\AbstractSender;

class AccountSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $text = "Мой аккаунт:";
        $buttons = [
            new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRED_USERS->value, 'Приглашенные пользователи'),
            new ButtonDto(CommonCallbackEnum::ACCOUNT_REFERRAL_LINK->value, 'Моя реферальная ссылка'),
            new ButtonDto(CommandEnum::START->value, 'Назад'),
        ];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }
}
