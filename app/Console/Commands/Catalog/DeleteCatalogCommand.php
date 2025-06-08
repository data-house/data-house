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
    protected $signature = 'catalog:delete {catalog}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a catalog and it\'s entries. This command is irreversible and should be used with caution.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $catalogRef = $this->argument('catalog');

        $catalog = Catalog::findOrFail($catalogRef);

        // if($catalog->entries()->exists()) {
        //     $this->error('This catalog has entries. Please delete the entries first.');
        //     return;
        // }

        $this->line("Deleting catalog {$catalog->title}...");

        $catalog->catalogValues()->delete();
        
        $catalog->entries()->delete();

        $catalog->fields()->delete();
        
        $catalog->delete();

        $this->info("Catalog {$catalog->title} deleted successfully.");
    }
}
