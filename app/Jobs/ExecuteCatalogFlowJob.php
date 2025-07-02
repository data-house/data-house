<?php

namespace App\Jobs;

use App\Actions\Catalog\Flow\ExecuteCatalogFlowOnDocument;
use App\Models\CatalogFlow;
use App\Models\CatalogFlowRun;
use App\Models\Document;
use App\Models\ImportStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteCatalogFlowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CatalogFlowRun $flowRun,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ExecuteCatalogFlowOnDocument $executeFlow): void
    {
        $flow = $this->flowRun->flow;
        $document = $this->flowRun->document;

        logs()->info("Executing flow [run = {$this->flowRun->getKey()}]", ['flow' => $flow->getKey(), 'document' => $document->getKey()]);

        $this->flowRun->status = ImportStatus::RUNNING;
        $this->flowRun->save();

        try {
            $result = $executeFlow($flow, $document, $this->flowRun);
    
            $this->flowRun->status = ImportStatus::COMPLETED;
            $this->flowRun->run_result = $result;
            $this->flowRun->save();
        } catch (\Throwable $th) {
            $this->flowRun->status = ImportStatus::FAILED;
            $this->flowRun->run_result = ['error' => $th->getMessage()];
            $this->flowRun->save();
        }
        
    }
}
