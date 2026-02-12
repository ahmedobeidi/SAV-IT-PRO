<?php

namespace App\Enum;

enum RepairOrderStatus: string {
    case CREATED = 'CREATED';
    case ASSIGNED = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case WAITING_PARTS = 'WAITING_PARTS';
    case DONE = 'DONE';
    case DELIVERED = 'DELIVERED';
    case CANCELED = 'CANCELED';
}
