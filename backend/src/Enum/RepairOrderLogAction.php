<?php

namespace App\Enum;

enum RepairOrderLogAction: string
{
    case CREATED = 'CREATED';
    case UPDATED = 'UPDATED';
    case ASSIGNED = 'ASSIGNED';
    case STATUS_CHANGED = 'STATUS_CHANGED';
    case ANONYMIZED = 'ANONYMIZED';
}
