<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case TECHNICIAN = 'TECHNICIAN';
    case RECEPTION = 'RECEPTION';
}
