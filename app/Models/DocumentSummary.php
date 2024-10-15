<?php

namespace App\Models;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Illuminate\Support\Str;

class DocumentSummary extends Model implements Htmlable
{
    use HasFactory;

    protected $fillable = [
        'language',
        'text',
        'ai_generated',
        'user_id',
        'all_document',
    ];

    protected $casts = [
        'language' => LanguageAlpha2::class,
        'ai_generated' => 'boolean',
        'all_document' => 'boolean',
    ];
    
    protected $attributes = [
        'ai_generated' => false,
    ];


    public function scopeWholeDocument($query)
    {
        $query->where('all_document', true);
    }

    public function scopePartDocument($query)
    {
        $query->where('all_document', false);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function section()
    {
        return $this->belongsTo(DocumentSection::class);
    }

    public function isAiGenerated(): bool
    {
        return $this->ai_generated;
    }

    public function isWholeDocument(): bool
    {
        return $this->all_document;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return Str::markdown($this->text);
    }
}
