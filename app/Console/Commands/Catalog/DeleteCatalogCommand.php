<?php

namespace App\Console\Commands\Catalog;

use App\Models\Catalog;
use Illuminate\Console\Command;
use Illuminate\Console\Prohibitable;

class DeleteCatalogCommand extends Command
{
    use Prohibitable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:delete {catalogs*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete one or more catalogs and their entries. This command is irreversible and should be used with caution.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $catalogRefs = $this->argument('catalogs');

        foreach ($catalogRefs as $catalogRef) {
            try {
                $catalog = Catalog::findOrFail($catalogRef);

                $this->line("Deleting catalog {$catalog->title}...");

                $catalog->catalogValues()->delete();
                
                $catalog->entries()->delete();

                $catalog->fields()->delete();
                
                $catalog->delete();

                $this->info("Catalog {$catalog->title} deleted successfully.");
            }
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->error("Catalog {$catalogRef} not found.");
                continue;
            }
        }
    }
}
