<?php

namespace App\Console\Commands;

use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ImportScheduleRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:schedule-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the scheduled imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /*
         * Get import maps with a schedule configuration
         * and then identity which ones are due
         */

        $schedulesMaps = ImportMap::query()
            ->with('import')
            ->notRunning()
            ->scheduled()
            ->orderBy('import_id', 'ASC')
            ->get()
            ->filter
            ->isDue();

        if($schedulesMaps->isEmpty()){
            $this->line("No import maps to run.");

            return self::SUCCESS;
        }

        $mapsPerImport = $schedulesMaps->groupBy('import.ulid');

        $this->line("{$schedulesMaps->count()} import map to run.");

        $mapsPerImport->each(function($maps, $importUlid): void{

            $import = $maps->first()->import;

            Cache::lock($import->lockKey())
                ->block(30, function() use ($maps) {
                    return DB::transaction(function () use ($maps) {

                        collect($maps)->each->resetStatusForRetry();

                        return true;
                    });
                });

            $import->start();

        });
        
    }
}
