<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class RemoveClassifierCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:remove-classifier
            {name : Classifier name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a classifier for this library.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->line("Removing [{$name}]...");

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $copilot = app(CopilotManager::class)->driver();

        $copilot->removeClassifier($name);

        $this->comment("Classifier removed.");
    }
}
