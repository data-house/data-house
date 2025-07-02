<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogFlowRun extends Model
{
    protected $fillable = [
        'catalog_flow_id',
        'user_id',
        'document_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'run_result' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function flow(): BelongsTo
    {
        return $this->belongsTo(CatalogFlow::class, 'catalog_flow_id');
    }
    
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query
            ->when(!$user->hasRole(Role::ADMIN->value), function ($query, $role) use ($user): void {
                $query->where('user_id', $user->getKey());
            });
    }
    
    public function scopeForDocument($query, Document $document)
    {
        return $query
            ->whereBelongsTo($document);
    }
    
    public function scopeWithStatus($query, ImportStatus $status)
    {
        return $query
            ->where('status', $status->value);
    }
    
    public function scopeRunning($query)
    {
        return $query
            ->where(fn ($q) => $q->where('status', ImportStatus::CREATED->value)->orWhere('status', ImportStatus::RUNNING->value))
            ;
    }
}
