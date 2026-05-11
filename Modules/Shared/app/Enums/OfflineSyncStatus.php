<?php

namespace Modules\Shared\Enums;

enum OfflineSyncStatus: int
{
    case Ok = 1;
    case Error = 2;
    case Skipped = 3;
    case Conflict = 4;
}
