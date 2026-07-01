<?php

namespace App\Enums;

enum SalesRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case REP = 'rep';
}
