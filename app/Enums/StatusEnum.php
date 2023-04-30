<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case PAID = 'PAID';
}
