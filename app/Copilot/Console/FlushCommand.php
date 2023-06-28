<?php

namespace App\Copilot\Console;

use App\Copilot\Events\ModelsUnquestionable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class FlushCommand extends Command
{
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
        $class = $this->argument('model');

        $model = new $class;

        $events->listen(ModelsUnquestionable::class, function ($event) use ($class) {
            $key = $event->models->last()->getScoutKey();

            $this->line('<comment>Removed ['.$class.'] models up to ID:</comment> '.$key);
        });

        $model::removeAllFromCopilot($this->option('chunk'));

        $events->forget(ModelsUnquestionable::class);

        $this->info('All ['.$class.'] records have been removed.');
    }
}
