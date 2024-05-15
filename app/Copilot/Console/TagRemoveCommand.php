<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @deprecated
 */
class TagRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:tag-remove {list : The name of the tag list to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a tag list from Copilot';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $list = $this->argument('list');

        $this->comment("Removing tag list [{$list}]");

        app(CopilotManager::class)->driver()->removeTagList($list);

        $this->info('Tag list ['.$list.'] removed.');
    }
}
