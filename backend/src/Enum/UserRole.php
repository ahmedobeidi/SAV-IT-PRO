<?php

namespace App\Enum;

enum UserRole: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case TECHNICIAN = 'ROLE_TECHNICIAN';
    case RECEPTION = 'ROLE_RECEPTION';
}
