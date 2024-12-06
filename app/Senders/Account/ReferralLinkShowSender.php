<?php

namespace App\Senders\Account;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ReferralLinkShowSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $referrerLink = config('services.telegram.botLink') . '?start=' . $this->user->referrer_link;

        $text = "🎓 Создавай тесты, играй в квиз с друзьями не выходя из телеграмма. ";
        $text .= "Участвуй в акциях и выигрывай ценные призы!\n\n";
        $text .= "🎲 Присоединяйся сейчас\n\n{$referrerLink}";

        $this->sendPhoto(
            imageUrl: asset('assets/img/referral.png'),
            text: $text,
            buttons: StateEnum::ACCOUNT_REFERRAL_LINK_SHOW->buttons()
        );
    }
}
