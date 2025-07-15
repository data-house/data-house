<?php

namespace App\Console\Commands\Catalog;

use App\Actions\Catalog\Flow\ExecuteCatalogFlowOnDocument;
use App\Models\CatalogFlow;
use App\Models\Document;
use Illuminate\Console\Command;
use \Illuminate\Support\Str;

class ExecuteCatalogFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:execute-flow {--flow= : The flow to execute.} {document : The identifier of the document}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute a catalog flow on a document.';

    /**
     * Execute the console command.
     */
    public function handle(ExecuteCatalogFlowOnDocument $executeFlow)
    {
        $flowInput = $this->option('flow');

        if(blank($flowInput)){

            $possibleFlows = CatalogFlow::query()->get()->map(fn($f) => "{$f->title} [id:{$f->getKey()}]")->all();

            $selection = $this->choice("Select the flow", $possibleFlows );

            $flowInput = is_integer($selection) ? $selection : Str::between($selection, '[id:', ']');
        }

        $flow = CatalogFlow::find($flowInput);

        $documentInput = $this->argument('document');

        $document = Str::isUlid($documentInput) ? Document::whereUlid($documentInput)->sole() : Document::findOrFail($documentInput);

        $this->line("Executing [{$flow->title}] on [{$document->title}]...");

        $extractions = $executeFlow($flow, $document);

        $countExtractions = count($extractions);

        $this->info("Execution completed. [{$countExtractions}] line(s) added to catalog [{$flow->catalog->title}].");
        
    }
}
