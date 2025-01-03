<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\SuggestDocumentAbstract;
use App\Copilot\Copilot;
use App\DocumentThumbnail\Facades\Thumbnail;
use App\Models\Document;
use App\Models\Role;
use App\Pipelines\PipelineTrigger;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class DocumentThumbnailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:thumbnail {documents?* : The Ulid of the documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnail for specified documents';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(Thumbnail::disabled()){
            $this->error(__('Thumbnail module disabled.'));
            return self::INVALID;
        }

        $ulids = $this->argument('documents') ?? [];

        $documents = Document::query()
            ->when(!empty($ulids), function($query) use ($ulids) {
                return $query->whereIn('ulid', $ulids);
            })
            ->whereNull('thumbnail_disk_path')
            ->get();

        $documents
            ->each(function($document): void {
                try {
                    /**
                    * @var \App\DocumentThumbnail\FileThumbnail
                    */
                    $convertedFile = Thumbnail::thumbnail($document->asReference());

                    $document->thumbnail_disk_name = $convertedFile->diskName();
                    $document->thumbnail_disk_path = $convertedFile->path();

                    $document->saveQuietly();

                    $this->line("Thumbnail generated for document [{$document->id} - {$document->ulid}]");
                } catch (\Throwable $th) {
                    $this->error("Thumbnail error for document [{$document->id} - {$document->ulid}] - {$th->getMessage()}");
                }
            });
        
        return self::SUCCESS;
    }

}
