<?php

namespace App\Models;


enum DocumentType: int
{
    case DOCUMENT = 1;
    case REPORT = 20;
    case PROJECT_REPORT = 21;
    case EVALUATION_REPORT = 22;
}
