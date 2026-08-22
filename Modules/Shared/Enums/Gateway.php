<?php

namespace Modules\Shared\Enums;

enum Gateway: int
{
    case PayMob = 1;
    case Stripe = 2;
}
