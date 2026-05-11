<?php

namespace Modules\Restaurant\Enums;

enum OrderStatus: int
{
    case Open = 1;
    case Sent = 2;
    case Ready = 3;
    case Paid = 4;
    case Cancelled = 5;
}
