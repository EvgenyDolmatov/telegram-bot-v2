<?php

namespace App\Senders\Account;

use App\Enums\StateEnum;
use App\Models\User;
use App\Senders\AbstractSender;

class ReferredUsersShowSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $text = 'У вас пока нет приглашенных пользователей.';
        $referredUsers = $this->user->referredUsers;

        if ($referredUsers->count()) {
            $text = StateEnum::ACCOUNT_REFERRED_USERS_SHOW->title();
            foreach ($referredUsers as $referredUser) {
                $refUser = User::find($referredUser->referred_user_id);

                if ($refUser && $refUser->username) {
                    $text .= "\n<a href='https://t.me/{$refUser->username}'>{$refUser->username}</a>\n";
                }
            }
        }

        $this->sendMessage($text, StateEnum::ACCOUNT_REFERRED_USERS_SHOW->buttons());
    }
}
