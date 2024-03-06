<?php

namespace App\Models;

use App\Data\ImportScheduleSettings;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMap extends Model
{
    use HasFactory;

    use HasUlids;


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'configuration',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'mapped_team',
        'mapped_uploader',
        'recursive',
        'filters',
        'visibility',
        'schedule',
        'last_executed_at',
    ];

    protected $casts = [
        'recursive' => 'boolean',
        'filters' => 'json',
        'status' => ImportStatus::class,
        'visibility' => Visibility::class,
        'schedule' => ImportScheduleSettings::class . ':default',
        'last_executed_at' => 'datetime',
    ];
    
    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['ulid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'ulid';
    }

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function documents()
    {
        return $this->hasMany(ImportDocument::class);
    }

    public function mappedTeam()
    {
        return $this->belongsTo(Team::class, 'mapped_team');
    }

    public function mappedUploader()
    {
        return $this->belongsTo(User::class, 'mapped_uploader');
    }

    public function scopeStatus($query, ImportStatus $status)
    {
        return $query->where('status', $status->value);
    }
    
    public function scopeNotRunning($query)
    {
        return $query
            ->where('status', '!=', ImportStatus::RUNNING->value);
    }

    public function scopeScheduled($query)
    {
        return $query
            ->whereNotNull('schedule')
            ->where('schedule->schedule', '!=', ImportSchedule::NOT_SCHEDULED->value);
    }

    public function lockKey(): string
    {
        return 'import-map-lock:' . $this->ulid;
    }

    public function label()
    {
        return $this->name ?? basename($this->filters['paths'][0]);
    }

    public function isStarted()
    {
        return $this->status === ImportStatus::RUNNING;
    }


    public function isScheduled()
    {
        return !is_null($this->schedule) && $this->schedule->isScheduled();
    }

    public function resetStatusForRetry()
    {
        $this->status = ImportStatus::CREATED;
        $this->save();
    }

    /**
     * Get the Cron expression for the event.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->schedule?->getCronExpression();
    }

    public function nextRunDate()
    {
        return $this->schedule?->nextRunDate();
    }

    public function isDue(): bool
    {
        return $this->schedule?->expressionPasses() ?? false;
    }
}
