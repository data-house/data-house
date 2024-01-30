<?php

namespace App\Models;


enum Flag: string
{
    case AI_QUESTION_WHOLE_LIBRARY = 'ai.question-whole-library';
    case DOCUMENT_VISIBILITY_EDIT = 'document.visibility.edit';
    case DOCUMENT_FILTERS_SOURCE = 'document.filters.source';
    case DASHBOARD = 'dashboard';




    public static function dashboard(): string
    {
        return static::DASHBOARD->value;
    }
    
    public static function sourceDocumentFilter(): string
    {
        return static::DOCUMENT_FILTERS_SOURCE->value;
    }
    
    public static function editDocumentVisibility(): string
    {
        return static::DOCUMENT_VISIBILITY_EDIT->value;
    }
    
    public static function questionWholeLibraryWithAI(): string
    {
        return static::AI_QUESTION_WHOLE_LIBRARY->value;
    }
}
