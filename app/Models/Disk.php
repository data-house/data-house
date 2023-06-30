<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

enum Disk: string
{
    case DOCUMENTS = 'documents';
    
    case IMPORTS = 'imports';
}
