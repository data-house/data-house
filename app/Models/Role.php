<?php

namespace App\Models;


enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case GUEST = 'guest';
}
