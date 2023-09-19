<?php

namespace App\Models;


enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CONTRIBUTOR = 'contributor';
    case GUEST = 'guest';
}
