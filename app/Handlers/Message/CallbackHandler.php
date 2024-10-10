<?php

namespace App\Handlers\Message;

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
            case CommonCallbackEnum::ADMIN_STATISTIC_MENU->value:
                $helper->adminStatisticMenu();
                return;
            case CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES->value:
                $helper->adminStatisticQuizzes();
                return;
            case CommonCallbackEnum::ADMIN_STATISTIC_QUIZZES_DAY->value:
                $helper->adminStatisticQuizzesPerDay();
                return;
            case CommonCallbackEnum::ADMIN_STATISTIC_USERS->value:
                $helper->adminStatisticUsers();
                return;
        }
    }
}
