<?php

namespace App\Models;

use App\HasNotes;
use audunru\EagerLoadPivotRelations\EagerLoadPivotTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LinkedDocument extends Pivot
{
    use EagerLoadPivotTrait;

    use HasNotes;
    
    public $incrementing = true;

    protected $table = 'collection_document';

    public function linkTypes(): BelongsToMany
    {
        return $this->belongsToMany(RelationType::class, 'linked_document_relation_type', 'linked_document_id')
            ->withTimestamps();
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
    
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
