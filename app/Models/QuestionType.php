<?php

namespace App\Models;

enum QuestionType: int
{
    case DESCRIPTIVE = 10;

    case CLASSIFICATION = 20;

    case COMPARATIVE = 30;

    case QUANTITATIVE = 40;
}
