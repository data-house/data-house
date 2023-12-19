<?php

namespace App\Jobs;

use App\Models\Disk;
use App\Models\Document;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use App\Models\ImportReport;
use App\Models\ImportStatus;
use App\Models\Visibility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MoveImportedDocumentsJob extends ImportJobBase
{

    public function runImport(): mixed
    {
        // TODO: verify again that user can access the specified team

        $rows = $this->importMap->documents()->whereNull('processed_at')->with(['user', 'team']);
        //TODO: skip entries that have errors

        $processed = 0;

        try {
            // Wait 180 seconds to try and acquire the lock
            Cache::lock($this->importMap->import->lockKey())->block(180, function () use ($rows, &$processed) {
                // If the Import has been cancelled, we don't want to insert anything
                if ($this->hasBeenCancelled()) {
                    return ;
                }

                DB::transaction(function() use ($rows, &$processed) {
                    $chunks = $rows->lazyById(20);

                    $chunks->each(function($chunk) use(&$processed) {
                        
                        // Create Document entry
                        $this->insertDocuments($chunk);

                        $processed+=$chunk->count();
                    });
                });
            });
        } catch (LockTimeoutException) {
            // Retry the job with a 20-second delay
            $this->release(20);
        }

        $nextPageToken = null;

        if($processed < $rows->count()){
            $entry = $rows->first();

            $nextPageToken = $entry->getKey() ?? 'non-empty';
        }

        return new ImportReport($processed, $nextPageToken);
    }

    protected function lastPage()
    {
        Cache::lock($this->importMap->import->lockKey())->block(30, function() {
            DB::transaction(function () {
                $this->importMap->status = ImportStatus::COMPLETED;

                $this->importMap->save();

                if(! $this->importMap->import->maps()->where('status', ImportStatus::RUNNING->value)->exists()){
                    $import = $this->importMap->import;

                    $import->status = ImportStatus::COMPLETED;
                    $import->save();
                }
            });
        });
    }


    protected function insertDocuments(ImportDocument $import)
    {
        // Checking for possible duplicates based on document hash
        // Focusing to documents that are accessible in the user's team

        if($import->team_id && !$import->user->hasTeamPermission($import->team, 'import:create')){
            $import->status = ImportDocumentStatus::CANCELLED_MISSING_PERMISSION;
    
            $import->save();

            return;
        }


        $teams = collect($import->team_id ?? $import->user?->allTeams()->modelKeys());

        if($import->document_hash && Document::where('document_hash', $import->document_hash)->whereIn('team_id', $teams)->exists()){
            $import->status = ImportDocumentStatus::SKIPPED_DUPLICATE;
    
            $import->save();

            return;
        }

        $path = $import->moveToDisk(Disk::DOCUMENTS->value);

        $checksum = Storage::disk(Disk::DOCUMENTS->value)->checksum($path, ['checksum_algo' => 'sha256']);

        if($import->document_hash && $import->document_hash !== $checksum){

            // Data transfer error

            $import->status = ImportDocumentStatus::FAILED;
    
            $import->save();

            return;
        }

        $document = Document::create([
            'disk_name' => Disk::DOCUMENTS->value,
            'disk_path' => $path,
            'title' => basename($import->source_path),
            'mime' => $import->mime,
            'uploaded_by' => $import->uploaded_by,
            'team_id' => $import->team_id,
            'document_hash' => $checksum,
            'document_date' => $import->document_date,
            'document_size' => $import->document_size,
            'visibility' => Visibility::defaultDocumentVisibility(),
        ]);

        $import->processed_at = now();
        $import->status = ImportDocumentStatus::COMPLETED;
        $import->document_id = $document->getKey();
        $import->save();
    }
}
