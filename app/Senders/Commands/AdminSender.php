<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AdminSender extends AbstractSender
{
    public function send(): void
    {
        if ($this->user->is_admin) {
            $buttons = [
                new ButtonDto(CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value, 'Создать рассылку'),
                new ButtonDto(CommonCallbackEnum::ADMIN_STATISTIC_MENU->value, 'Статистика бота'),
                new ButtonDto(CommandEnum::START->value, 'Вернуться в начало')
            ];

            $message = $this->messageBuilder->createMessage(StateEnum::ADMIN->title(), $buttons);
            $this->senderService->sendMessage($message);
        }

        $this->someProblemMessage();
    }
}
