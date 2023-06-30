<?php

namespace App\Models;


enum ImportStatus: int
{
    case CREATED   = 10;
    case RUNNING   = 20;
    case COMPLETED = 30;
    case CANCELLED = 40;
    case FAILED    = 50;
}
