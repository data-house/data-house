<?php

namespace App\Jobs;

use App\Models\Disk;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use App\Models\ImportMap;
use App\Models\ImportReport;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Str;
use League\Flysystem\InvalidStreamProvided;
use Throwable;

class ImportFileDataJob extends ImportJobBase
{

    public ?string $nextPage = null;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportMap $map, ?string $nextPage = null)
    {
        parent::__construct($map);

        $this->nextPage = $nextPage;
    }

    /**
     * Execute the job.
     */
    public function runImport(): mixed
    {     
        /**
         * @var \Illuminate\Contracts\Filesystem\Filesystem
         */
        $disk = $this->importMap->import->connection();
        
        $processed = 0;
        $lastProcessed = null;

        $this->importMap
            ->documents()
            ->whereNull('retrieved_at')
            ->take(10)
            ->lazyById(5)
            ->each(function($importDocument) use ($processed, $lastProcessed, $disk) {

                $importDocument->status = ImportDocumentStatus::IMPORTING;

                $this->downloadDocument($disk, $importDocument);

                $lastProcessed = $importDocument->getKey();
                $processed++;
            });

        return new ImportReport($processed, $lastProcessed);
    }

    protected function lastPage()
    {
        dispatch(new MoveImportedDocumentsJob($this->importMap));
    }

    protected function downloadDocument(Filesystem $disk, ImportDocument $document)
    {

        $localPath = $document->generateLocalPath();

        $importDisk = Storage::disk($document->disk_name);

        try{
            $importDisk->writeStream($localPath, $disk->readStream($document->source_path));
        }
        catch(InvalidStreamProvided $th){

            logs()->error("Import file data download error", ['ex' => $th, 'import_document' => $document->getKey()]);

            $document->status = ImportDocumentStatus::SKIPPED_MISSING_SOURCE;
    
            $document->save();

            return;
        }
        catch(Throwable $th){

            logs()->error("Import file data download error", ['ex' => $th, 'import_document' => $document->getKey()]);

            $document->status = ImportDocumentStatus::FAILED_DOWNLOAD_ERROR;
    
            $document->save();

            return;
        }
        
        $document->document_hash = $importDisk->checksum($localPath, ['checksum_algo' => 'sha256']);
        $document->retrieved_at = now();
        $document->disk_path = $localPath;

        $document->save();
    }
}
