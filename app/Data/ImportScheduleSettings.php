<?php

namespace App\Data;

use App\Models\ImportSchedule;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\ManagesFrequencies;
use Illuminate\Support\Facades\Date;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ImportScheduleSettings extends Data
{
    
    public function __construct(
        /**
         * The schedule to apply
         */
        #[WithCast(EnumCast::class)]
        public ImportSchedule $schedule = ImportSchedule::NOT_SCHEDULED,

        /**
         * The explicit Cron expression if $schedule is set to custom
         */
        public ?string $expression = null,
    ) {
    }


    public function label(): string
    {
        return str($this->schedule->name)->replace('_', ' ')->title()->toString();
    }

    public function isScheduled(): bool
    {
        return $this->schedule !== ImportSchedule::NOT_SCHEDULED;
    }
    
    public function is(ImportSchedule $schedule): bool
    {
        return $this->schedule === $schedule;
    }


    public function getCronExpression(): ?string
    {
        return $this->schedule === ImportSchedule::NOT_SCHEDULED ? null : ($expression ?? $this->schedule->getExpression());
    }

    /**
     * Determine the next due date for the import map.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function nextRunDate()
    {
        $cron = $this->getCronExpression();

        if(is_null($cron) || !$this->isScheduled()){
            return null;
        }

        return Date::instance((new CronExpression($cron))
            ->getNextRunDate('now', 0, false));
    }

    /**
     * Determine if the Cron expression passes.
     */
    public function expressionPasses(): bool
    {
        $cron = $this->getCronExpression();

        if(is_null($cron) || !$this->isScheduled()){
            return false;
        }

        $date = Date::now();

        return (new CronExpression($cron))->isDue($date->toDateTimeString());
    }
}
