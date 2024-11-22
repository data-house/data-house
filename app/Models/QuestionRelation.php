<?php

namespace App\Models;


enum QuestionRelation: int
{
    /**
     * A generic association between source and target entity
     */
    case CONNECT = 1;

    /**
     * The target entity was created from the source entity.
     * This can be used to represent an entity being
     * created from a template
     */
    case CREATE = 10;

    /**
     * The target entity is a child of source
     */
    case CHILDREN = 15;
    
    /**
     * The target entity is a new version of source
     */
    case REVISION = 20;
    
    /**
     * The target is a re-execution of source
     */
    case RETRY = 21;
    
    /**
     * The target entity is a revision of source
     */
    case TRANSLATION = 25;


}
