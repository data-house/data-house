<?php

namespace App\Console\Commands;

use App\Actions\SuggestDocumentTags;
use App\Copilot\Copilot;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Throwable;

class DocumentTagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:tag {documents?* : The Ulid of the documents} {--list= : The name of the tag list to use}, {--json= : The path to the file where results are saved in JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tag documents using a given tag list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(Copilot::disabled() || ! Copilot::hasTaggingFeatures()){
            $this->error(__('Tagging module disabled.'));
            return self::INVALID;
        }

        $ulids = $this->argument('documents') ?? [];

        $topicList = $this->option('list');
        
        $jsonOutputFile = $this->option('json');

        if (empty($topicList) && $this->input->isInteractive()) {
            $topicList = $this->ask("Specify the tag list to use for automatic tagging");
        }

        if (empty($topicList)) {
            $this->error(__('Tag list required. Specify a list using --list.'));
            return self::INVALID;
        }

        $action = new SuggestDocumentTags();

        $count = Document::query()
            ->when(!empty($ulids), function($query) use ($ulids) {
                return $query->whereIn('ulid', $ulids);
            })
            ->count();

        $this->line("Documents to process: {$count}.");
        
        $documents = Document::query()
            ->when(!empty($ulids), function($query) use ($ulids) {
                return $query->whereIn('ulid', $ulids);
            })
            ->chunkMap(function($document) use ($action, $topicList, $jsonOutputFile){

                try {
                    $this->line("Document [{$document->id} - {$document->ulid}]");
         
                    $tags = $action($topicList, $document);

                    if(is_null($jsonOutputFile)){
                        $this->table(
                            ['tag_name', 'score'],
                            $tags->map(fn($entry) => Arr::only($entry,['tag_name', 'score']))
                        );
                    }

                    return [
                        'id' => $document->id,
                        'ulid' => $document->ulid,
                        'title' => $document->title,
                        'tags' => $tags,
                    ];
                   
                } catch (Throwable $th) {

                    $this->error("ERROR Document [{$document->id} - {$document->ulid}] " . $th->getMessage());
                    
                    return null;
                }
            })
            ->filter();
        
        if($jsonOutputFile){
            $this->comment("Saving output to {$jsonOutputFile}");

            file_put_contents($jsonOutputFile, $documents->toJson());
        }
        
        return self::SUCCESS;
    }
}
