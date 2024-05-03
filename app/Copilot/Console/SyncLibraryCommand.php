<?php

namespace App\Copilot\Console;

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
    protected $signature = 'copilot:sync-library';

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
        
    }
}
