<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class RegisterClassifierCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:add-classifier
            {name : Classifier name}
            {url : Url on which the classification endpoint is reachable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a classifier for this library.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');

        $url = $this->argument('url');

        $this->line("Registering classifier...");

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $copilot = app(CopilotManager::class)->driver();

        $classifierId = $copilot->addClassifier($name, $url);

        $this->comment("Classifier registered as [{$classifierId}].");
    }
}
