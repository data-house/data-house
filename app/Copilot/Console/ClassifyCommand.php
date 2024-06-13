<?php

namespace App\Copilot\Console;

use App\Copilot\CopilotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class ClassifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copilot:classify-model
            {file : The file containing the topic list to import}
            {--c|classifier= : The classifier to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify a model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $file = $this->argument('file');

        $absolutePath = realpath($file);

        $this->line("Creating topic list");

        $this->comment("Reading topics from [{$absolutePath}]");

        $topicList = json_decode(file_get_contents($absolutePath), true);

        $listName = $topicList['topic_list_id'] ?? $topicList['id'];

        app(CopilotManager::class)->driver()->defineTagList($listName, $topicList['topics']);

        $this->info('Topic list ['.$listName.'] created.');
    }
}
