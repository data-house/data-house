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
    ];

    protected $casts = [
        'language' => LanguageAlpha2::class,
        'ai_generated' => 'boolean',
    ];
    
    protected $attributes = [
        'ai_generated' => false,
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAiGenerated(): bool
    {
        return $this->ai_generated;
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
