<?php

namespace App\Enums;

enum CommonCallbackEnum: string
{
    /** Account */
    case ACCOUNT_REFERRAL_LINK = 'account_referral_link';
    case ACCOUNT_REFERRED_USERS = 'account_referred_users';

    /** Admin */
    case ADMIN_CREATE_NEWSLETTER = 'admin_create_newsletter';
    case ADMIN_CONFIRM_NEWSLETTER = 'admin_confirm_newsletter';

    /** Admin statistic */
    case ADMIN_STATISTIC_MENU = 'admin_statistic_menu';
    case ADMIN_STATISTIC_QUIZZES = 'admin_statistic_quizzes';
    case ADMIN_STATISTIC_QUIZZES_DAY = 'admin_statistic_quizzes_day';
    case ADMIN_STATISTIC_QUIZZES_WEEK = 'admin_statistic_quizzes_week';
    case ADMIN_STATISTIC_QUIZZES_MONTH = 'admin_statistic_quizzes_month';
    case ADMIN_STATISTIC_QUIZZES_QUARTER = 'admin_statistic_quizzes_quarter';
    case ADMIN_STATISTIC_QUIZZES_YEAR = 'admin_statistic_quizzes_year';
    case ADMIN_STATISTIC_USERS = 'admin_statistic_users';
    case ADMIN_STATISTIC_USERS_DAY = 'admin_statistic_users_day';

    /** Common */
    case SUPPORT = 'support';
}
