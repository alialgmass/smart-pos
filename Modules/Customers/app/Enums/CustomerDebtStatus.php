<?php

namespace Modules\Customers\Enums;

enum CustomerDebtStatus: int
{
    case Open = 1;
    case PartialPaid = 2;
    case Paid = 3;
    case WrittenOff = 4;
}
