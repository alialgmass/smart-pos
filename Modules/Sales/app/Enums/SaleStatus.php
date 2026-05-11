<?php

namespace Modules\Sales\Enums;

enum SaleStatus: int
{
    case Completed = 1;
    case Refunded = 2;
    case PartiallyRefunded = 3;
    case Voided = 4;
}
