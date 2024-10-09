<?php

namespace App\Handlers;

use App\Enums\CommonCallbackEnum;

class CallbackHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        $helper = $this->helper;

        switch ($message) {
            case CommonCallbackEnum::SUPPORT:
                $helper->support();
                return;
            case CommonCallbackEnum::ACCOUNT_REFERRAL_LINK->value:
                $helper->showReferralLink();
                return;
            case CommonCallbackEnum::ACCOUNT_REFERRED_USERS->value:
                $helper->showReferredUsers();
                return;
            case CommonCallbackEnum::ADMIN_CREATE_NEWSLETTER->value:
                $helper->adminNewsletterWaiting();
                return;
            case CommonCallbackEnum::ADMIN_CONFIRM_NEWSLETTER->value:
                $helper->adminNewsletterSent();
                return;
        }
    }
}
