<?php

namespace App\Copilot\Console;

use App\Copilot\Events\ModelsQuestionable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:import
            {model : Class name of model to bulk import}
            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `copilot.chunk.questionable`)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the given model into the Copilot';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        $class = $this->argument('model');

        $model = new $class;

        $events->listen(ModelsQuestionable::class, function ($event) use ($class) {
            $key = $event->models->last()->getKey();

            $this->line('<comment>Imported ['.$class.'] models up to ID:</comment> '.$key);
        });

        $model::addAllToCopilot($this->option('chunk'));

        $events->forget(ModelsQuestionable::class);

        $this->info('All ['.$class.'] records have been imported.');
    }
}
