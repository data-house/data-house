<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use App\Copilot\Events\ModelsQuestionable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class SyncLibraryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:sync-library-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync library settings in Copilot';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        $copilot = app(CopilotManager::class)->driver();

        $copilot->syncLibrarySettings();
        
        $this->info("Settings for the [{$copilot->getLibrary()}] library synced successfully.");
    }
}
