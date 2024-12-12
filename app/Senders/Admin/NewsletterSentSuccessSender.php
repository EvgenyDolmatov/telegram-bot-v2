<?php

namespace App\Senders\Admin;

use App\Models\Newsletter;
use App\Models\User;
use App\Senders\AbstractSender;

class NewsletterSentSuccessSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        if (!$this->user->isAdmin() || !$newsletter = $this->user->newsletters->last()) {
            $this->someProblemMessage();
            return;
        }

        $chatIds = array_map(fn ($user) => $user['tg_chat_id'], User::all()->toArray());
        foreach ($chatIds as $chatId) {
            $this->sendNewsletterMessage($newsletter, $chatId);
        }
    }

    private function sendNewsletterMessage(Newsletter $newsletter, int $chatId): void
    {
        if ($newsletter->image) {
            $this->sendPhoto(
                imageUrl: asset($newsletter->image),
                text: $newsletter->text,
                isTrash: false,
                chatId: $chatId
            );
            return;
        }

        $this->sendMessage(
            text: $newsletter->text,
            isTrash: false,
            chatId: $chatId
        );
    }
}
