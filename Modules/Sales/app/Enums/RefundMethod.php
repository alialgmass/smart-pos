<?php

namespace Modules\Sales\Enums;

enum RefundMethod: int
{
    case Cash = 1;
    case Card = 2;
    case Deferred = 3;
}
