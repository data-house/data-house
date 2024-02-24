<?php

namespace App\Models;


enum Flag: string
{
    case AI_QUESTION_WHOLE_LIBRARY = 'ai.question-whole-library';
    case DOCUMENT_VISIBILITY_EDIT = 'document.visibility.edit';
    case DOCUMENT_FILTERS_SOURCE = 'document.filters.source';
    case DOCUMENT_FILTERS_TYPE = 'document.filters.type';
    case PROJECT_FILTERS_TYPE = 'project.filters.type';
    case PROJECT_FUNDING = 'project.funding';
    case DASHBOARD = 'dashboard';
    case COLLECTIONS = 'collections';




    public static function collections(): string
    {
        return static::COLLECTIONS->value;
    }

    public static function dashboard(): string
    {
        return static::DASHBOARD->value;
    }
    
    public static function sourceDocumentFilter(): string
    {
        return static::DOCUMENT_FILTERS_SOURCE->value;
    }
    
    public static function typeDocumentFilter(): string
    {
        return static::DOCUMENT_FILTERS_TYPE->value;
    }
    
    public static function typeProjectFilter(): string
    {
        return static::PROJECT_FILTERS_TYPE->value;
    }
    
    public static function editDocumentVisibility(): string
    {
        return static::DOCUMENT_VISIBILITY_EDIT->value;
    }
    
    public static function questionWholeLibraryWithAI(): string
    {
        return static::AI_QUESTION_WHOLE_LIBRARY->value;
    }
    
    public static function showProjectFunding(): string
    {
        return static::PROJECT_FUNDING->value;
    }
}
