<?php

namespace App\Models;


enum DocumentFormat: int
{
    case DOCUMENT = 10;
    case PRESENTATION = 20;
    case SPREADSHEET = 30;
    case AUDIO = 40;
    case VIDEO = 50;
    case IMAGE = 60;
}
