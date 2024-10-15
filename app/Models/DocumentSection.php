<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'order',
        'level',
        'reference',
        'user_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'level' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function summaries(): HasMany
    {
        return $this->hasMany(DocumentSummary::class)->partDocument()->latest();
    }

    public function latestSummary(): HasOne
    {
        return $this->hasOne(DocumentSummary::class)->latestOfMany();
    }

    public function scopeSortedByOrder($query, $direction = 'ASC')
    {
        return $query->orderBy('order', $direction);
    }
}
