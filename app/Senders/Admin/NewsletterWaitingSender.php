<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class NewsletterWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::ADMIN_NEWSLETTER_WAITING->title(),
            buttons: StateEnum::ADMIN_NEWSLETTER_WAITING->buttons()
        );
    }
}