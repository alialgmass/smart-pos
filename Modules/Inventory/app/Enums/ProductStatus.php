<?php

namespace Modules\Inventory\Enums;

enum ProductStatus: int
{
    case Active = 1;
    case Inactive = 2;
    case OutOfStock = 3;
}
