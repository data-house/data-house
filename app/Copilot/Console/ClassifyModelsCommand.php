<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use App\Models\Disk;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Throwable;

class ClassifyModelsCommand extends Command implements PromptsForMissingInput
{
    use InteractWithModels;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:classify-model
            {model : Class name of model to classify}
            {ids?* : The model identifiers}
            {--c|classifier= : The classifier to use}
            {--force= : Re-classify the model if previously classified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify a questionable model content using the specified classifier.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $class = $this->qualifyModel($this->argument('model'));

        $ids = $this->argument('ids');

        $classifier = $this->option('classifier');
        
        $force = $this->option('force') ?? false;

        if (empty($classifier) && $this->input->isInteractive()) {
            $classifier = $this->ask("Specify the classifier to use");
            if (empty($classifier)) {
                throw new Exception('Missing classifier name');
            }
        }
        else if (empty($classifier) && !$this->input->isInteractive()) {
            throw new Exception('Missing classifier name');
        }

        $this->line("Classifying [{$class}] using [{$classifier}]...");

        $bar = $this->output->createProgressBar();

        $bar->setFormat(' [%current%] %message% %elapsed:16s%');

        $bar->start();

        $bar->setMessage("Starting...");

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $copilot = app(CopilotManager::class)->driver();

        $disk = Storage::disk(Disk::DOCUMENT_CLASSIFICATION_RESULTS->value);

        $this->getModels($class, $ids)
            ->each(function ($modelInstance) use ($bar, $copilot, $disk, $classifier, $force) {
                $bar->setMessage("Classifying [{$modelInstance->getKey()} - {$modelInstance->ulid}]");
                $bar->advance();
                
                try {

                    if($disk->exists("{$modelInstance->ulid}/{$classifier}.json") && !$force){
                        return ;
                    }
                    
                    $classification = $copilot->classify($classifier, $modelInstance);

                    $disk->put("{$modelInstance->ulid}/{$classifier}.json", $classification->toJson());

                } catch (Throwable $th) {
                    $this->error("[{$modelInstance->getKey()} - {$modelInstance->ulid}] {$th->getMessage()}");
                }

            });

        $bar->finish();

        $this->newLine();

        $this->line('Models processed.');
    }

    protected function getModels($class, $ids = []): LazyCollection
    {
        if(empty($ids)){
            return $class::getAllQuestionableLazily();
        }

        $self = new $class;

        return $self->newQuery()
            ->when(true, function ($query) use ($self) {
                $query->where(fn($query) => $self->addAllToCopilotUsing($query));
            })
            ->whereIn($self->getCopilotKeyName(), $ids)
            ->lazyById();
    }
}
