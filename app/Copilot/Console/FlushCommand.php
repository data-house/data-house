<?php

namespace App\Copilot\Console;

use App\Copilot\Events\ModelsUnquestionable;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Throwable;

class FlushCommand extends Command
{
    use InteractWithModels;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:flush
        {model : Class name of the model to flush}
        {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `copilot.chunk.unquestionable`)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Flush all of the model's records from Copilot";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        $class = $this->qualifyModel($this->argument('model'));

        $model = new $class;

        $bar = $this->output->createProgressBar();

        $bar->setFormat(' [%current%] %message% %elapsed:16s%');

        $bar->start();

        $model::getAllQuestionableLazily()
            ->each(function ($modelInstance) use ($bar): void {
                $bar->setMessage("Removing [{$modelInstance->getKey()} - {$modelInstance->ulid}]");
                $bar->advance();

                try {
                    $modelInstance->unquestionable();
                } catch (Throwable $th) {
                    $this->error("[{$modelInstance->getKey()} - {$modelInstance->ulid}] {$th->getMessage()}");
                }
            });

        $bar->finish();

        $this->newLine();

        $this->info('All ['.$class.'] records have been removed.');
    }
}
