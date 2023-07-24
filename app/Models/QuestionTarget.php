<?php

namespace App\Models;

enum QuestionTarget: int
{
    case SINGLE = 10;

    case MULTIPLE = 20;

    // although derivable from the entity in the questionable morph
    // relation for some optimizations can be a good option to have it

}
