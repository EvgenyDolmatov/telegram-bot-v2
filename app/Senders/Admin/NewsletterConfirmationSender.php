<?php

namespace App\Senders\Admin;

use App\Senders\AbstractSender;

class NewsletterConfirmationSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        // TODO: Здесь нужно вывести предварительное сообщение с кнопками "Подтвердить" и "Изменить"
    }
}
