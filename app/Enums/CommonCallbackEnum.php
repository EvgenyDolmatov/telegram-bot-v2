<?php

namespace App\Enums;

enum CommonCallbackEnum: string
{
    /** Account */
    case ACCOUNT_REFERRAL_LINK = 'account_referral_link';
    case ACCOUNT_REFERRED_USERS = 'account_referred_users';

    /** Admin */
    case ADMIN_CREATE_NEWSLETTER = 'admin_create_newsletter';

    /** Common */
    case SUPPORT = 'support';
}
