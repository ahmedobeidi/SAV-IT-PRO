<?php

namespace App\Enum;

enum RepairOrderStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case WAITING_PARTS = 'WAITING_PARTS';
    case DONE = 'DONE';
    case CANCELLED = 'CANCELLED';
}
