<?php

namespace App\Models;

enum CollectionStrategy: int
{
    /**
     * Selected documents.
     * Collection content is bound to the static selection of documents as given at creation
     */
    case STATIC = 10;

    /**
     * All documents in the library.
     * Collection contais all documents in the library
     */
    case LIBRARY = 20;

    /**
     * Documents found using a search
     * Collections created from search results are examples of a dynamic collection
     */
    case SEARCH = 30;
}
