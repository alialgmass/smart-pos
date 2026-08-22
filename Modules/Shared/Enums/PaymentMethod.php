<?php

namespace Modules\Shared\Enums;

enum PaymentMethod: int
{
    case Cash = 1;
    case Card = 2;
    case Mixed = 3;
    case Deferred = 4;
}
