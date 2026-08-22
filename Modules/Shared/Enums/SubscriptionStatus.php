<?php

namespace Modules\Shared\Enums;

enum SubscriptionStatus: int
{
    case Trialing = 1;
    case Active = 2;
    case PastDue = 3;
    case Grace = 4;
    case ReadOnly = 5;
    case Cancelled = 6;
}
