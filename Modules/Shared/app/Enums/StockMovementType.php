<?php

namespace Modules\Shared\Enums;

enum StockMovementType: int
{
    case Purchase = 1;
    case Sale = 2;
    case Return = 3;
    case Adjustment = 4;
    case OfflineSync = 5;
}
