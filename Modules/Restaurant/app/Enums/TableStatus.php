<?php

namespace Modules\Restaurant\Enums;

enum TableStatus: int
{
    case Available = 1;
    case Occupied = 2;
    case Reserved = 3;
}
