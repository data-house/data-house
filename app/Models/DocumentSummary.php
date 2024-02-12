<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class DocumentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'text',
    ];

    protected $casts = [
        'language' => LanguageAlpha2::class,
        'ai_generated' => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
