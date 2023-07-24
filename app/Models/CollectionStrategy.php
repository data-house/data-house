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
     * All documents, a shortcut to refer to all visible documents to the user
     */
    case ALL_DOCUMENTS = 20;

    /**
     * Documents found using a search
     * Collections created from search results are examples of a dynamic collection
     */
    case SEARCH = 30;
}
