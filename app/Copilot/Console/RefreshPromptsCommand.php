<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class RefreshPromptsCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:refresh-prompts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a refresh of the configured prompts.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var \App\Copilot\Engines\Engine
         */
        $copilot = app(CopilotManager::class)->driver();

        $this->comment($copilot->refreshPrompts());
    }
}
