<?php

namespace Modules\Shared\Enums;

enum GatewayPaymentStatus: int
{
    case Pending = 1;
    case Succeeded = 2;
    case Failed = 3;
    case Cancelled = 4;
    case Expired = 5;
}
