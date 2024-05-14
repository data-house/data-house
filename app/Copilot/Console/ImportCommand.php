<?php

namespace App\Copilot\Console;

use Carbon\CarbonInterval;
use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;
use App\Copilot\Events\ModelsQuestionable;
use Illuminate\Contracts\Events\Dispatcher;

class ImportCommand extends Command
{
    use InteractWithModels;

    use InteractsWithTime;

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
        $startTime = microtime(true);

        $class = $this->qualifyModel($this->argument('model'));

        $model = new $class;

        $events->listen(ModelsQuestionable::class, function ($event) use ($class) {
            $key = $event->models->last()->getKey();

            $this->line('<comment>Imported ['.$class.'] models up to ID:</comment> '.$key);
        });

        $model::addAllToCopilot($this->option('chunk'));

        $events->forget(ModelsQuestionable::class);

        $runTime = $this->runTimeForHumans($startTime);

        $this->info("All [{$class}] records have been imported. ({$runTime})");
    }


    /**
     * Given a start time, format the total run time for human readability.
     *
     * @param  float  $startTime
     * @param  float  $endTime
     * @return string
     */
    protected function runTimeForHumans($startTime, $endTime = null)
    {
        // TODO: Remove this method before upgrading to Laravel 11
        $endTime ??= microtime(true);

        $runTime = ($endTime - $startTime) * 1000;

        return $runTime > 1000
            ? CarbonInterval::milliseconds($runTime)->cascade()->forHumans(short: true)
            : number_format($runTime, 2).'ms';
    }
}
