<?php

namespace App\Console\Commands;

use App\Exports\ExportQuestionFeedback;
use Illuminate\Console\Command;

class FeedbackExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export question/answer feedback';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new ExportQuestionFeedback)->store('feedbacks.csv', 'local');
    }
}
