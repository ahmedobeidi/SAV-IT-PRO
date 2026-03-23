<?php

namespace App\Enum;

enum RepairOrderLogAction: string
{
    case CREATED = 'CREATED';
    case UPDATED = 'UPDATED';
    case STATUS_CHANGED = 'STATUS_CHANGED';
    case ASSIGNED = 'ASSIGNED';
    case UNASSIGNED = 'UNASSIGNED';
    case PDF_GENERATED = 'PDF_GENERATED';
}