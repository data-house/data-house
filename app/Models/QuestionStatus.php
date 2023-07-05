<?php

namespace App\Models;

enum QuestionStatus: int
{
    case CREATED = 10;

    case PROCESSING = 20;

    case ANSWERING = 30;

    case CANCELLED = 40; 

    case ERROR = 50; 
    
    case PROCESSED = 60; 
}
