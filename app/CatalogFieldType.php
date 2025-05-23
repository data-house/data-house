<?php

namespace App;

enum CatalogFieldType: int
{
    /**
     * A single or multiline field
     */
    case TEXT = 10; // both single line and multiline

    /**
     * A number field (integer or float)
     */
    case NUMBER = 20;

    /**
     * A date and time field
     */
    case DATETIME = 30;

    /**
     * A yes/no field
     */
    case BOOLEAN = 40;

    /**
     * A single SKOS Concept contained in a specific SKOS Collection
     */
    case SKOS_CONCEPT = 50;
}
