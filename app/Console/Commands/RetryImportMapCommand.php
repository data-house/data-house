<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RetryImportMapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:retry-map {map}
                            {--force : Force a retry of a successful import map.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry an import map';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mapKey = $this->argument('map');

        $force = $this->option('force');
        
        $map = Str::isUlid($mapKey) ? ImportMap::whereUlid($mapKey)->with('import')->firstOrFail() : ImportMap::findOrFail($mapKey)->load('import');

        $import = $map->import;

        if($map->status !== ImportStatus::COMPLETED && $map->status !== ImportStatus::FAILED){
            $this->error("Import map is required to be completed or failed. Found [{$map->status->name}].");
            return self::INVALID;
        }
        
        if($map->status === ImportStatus::COMPLETED && ! $force){
            $this->error("Import map completed. Use --force to retry a completed import map.");
            return self::INVALID;
        }
        
        Cache::lock($map->import->lockKey())->block(30, function() use ($map) {
            return DB::transaction(function () use ($map) {

                $map->status = ImportStatus::CREATED;
                $map->save();

                return true;
            });
        });
        
        $import->start();

        $this->line("Import map retried.");
        
        return self::SUCCESS;
    }

}
