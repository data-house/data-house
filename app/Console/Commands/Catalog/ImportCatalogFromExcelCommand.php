<?php

namespace App\Console\Commands\Catalog;

use App\Actions\Catalog\CreateCatalog;
use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Models\Document;
use App\Models\Project;
use App\Models\SkosConcept;
use App\Models\User;
use App\Models\Visibility;
use DateTime;
use DateTimeImmutable;
use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelReader;
use \Illuminate\Support\Str;

class ImportCatalogFromExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a catalog from an Excel file';

    /**
     * Execute the console command.
     */
    public function handle(CreateCatalog $createCatalog, CreateCatalogField $createField, CreateCatalogEntry $createEntry): void
    {
        $file = $this->argument('file');

        $user = User::find(1);

        if(file_exists($file) === false) {
            $this->error("File does not exist: {$file}");
            return;
        }


        $this->line('Inspecting file structure...');

        $reader = SimpleExcelReader::create($file)
            ->trimHeaderRow();


        $columns = collect($reader->getHeaders());
        
        $rows = $reader
            ->getRows()
            ->remember();
        

        $columnsValuePreview = $columns->mapWithKeys(function($column) use ($rows) {
            return [$column => collect($rows->take(2)->pluck($column)->filter()->values()->all())];
        });

        $possibleFields = $columnsValuePreview->mapWithKeys(function($values, $column) {

            if($values->isEmpty()) {
                // If the column is empty, we cannot determine the type, so we default to text
                return [$column => CatalogFieldType::TEXT];
            }

            if($values->filter(fn($value) => is_numeric($value))->isNotEmpty()) {
                return [$column => CatalogFieldType::NUMBER];
            }
            
            if($values->whereInstanceOf(DateTime::class)->isNotEmpty()) {
                return [$column => CatalogFieldType::DATETIME];
            }
            
            if($values->whereInstanceOf(DateTimeImmutable::class)->isNotEmpty()) {
                return [$column => CatalogFieldType::DATETIME];
            }

            $stringyValues = $values->filter(fn($value) => is_string($value))->map(fn($value) => trim($value));

            if($stringyValues->isNotEmpty() && SkosConcept::whereIn('pref_label', $stringyValues)->orWhereIn('notation', $stringyValues)->exists()){
                // Check if terms are exact concepts, if yes take the skos collection that contains them, if any
                $concepts = SkosConcept::whereIn('pref_label', $stringyValues)
                    ->orWhereIn('notation', $stringyValues)
                    ->with('collections')
                    ->get()
                    ->pluck('collections')
                    ->flatten()
                    ->unique('id');

                if($concepts->isNotEmpty() && $concepts->count() === 1){
                    return [$column => [
                        'type' => CatalogFieldType::SKOS_CONCEPT,
                        'concept_collection' => $concepts->first(),
                    ]];
                }

                if($concepts->isNotEmpty() && $concepts->count() > 1 && $conceptWithColumnName = $concepts->where('pref_label', $column)->first()){
                    return [$column => [
                        'type' => CatalogFieldType::SKOS_CONCEPT,
                        'concept_collection' => $conceptWithColumnName,
                    ]];
                }

            } 

            if($stringyValues->map(fn($value) => strlen($value))->max() <= 100) {
                return [$column => CatalogFieldType::TEXT];
            }

            return [$column => CatalogFieldType::MULTILINE_TEXT];
        });

        $possibleEntryIndexColumn = $possibleFields->only(['No', 'ID', 'Index', 'Entry No','no', 'id', 'index', 'entry no']);
        
        $possibleEntryDocumentColumn = $possibleFields->only(['Document', 'Document ID', 'Document Ulid', 'document_ulid', 'Document Ref', 'document', 'document id', 'document ulid' , 'document ref',]);
        
        $possibleEntryProjectColumn = $possibleFields->only(['Project', 'Project ID', 'Project Ref', 'Project Ulid', 'project_ulid', 'project', 'project id', 'project ulid', 'project ref', ]);

        if($possibleEntryIndexColumn->isNotEmpty() && $possibleEntryIndexColumn->count() > 1) {
            $this->error("Ambiguous index column, found {$possibleEntryIndexColumn->count()} candidates: {$possibleEntryIndexColumn->join(',')}");
            return;
        }

        if($possibleEntryDocumentColumn->isNotEmpty() && $possibleEntryDocumentColumn->count() > 1) {
            $this->error("Ambiguous document reference column, found {$possibleEntryDocumentColumn->count()} candidates: {$possibleEntryDocumentColumn->join(',')}");
            return;
        }

        if($possibleEntryProjectColumn->isNotEmpty() && $possibleEntryProjectColumn->count() > 1) {
            $this->error("Ambiguous Project reference column, found {$possibleEntryProjectColumn->count()} candidates: {$possibleEntryProjectColumn->join(',')}");
            return;
        }

        $entryIndexColumn = $possibleEntryIndexColumn->keys()->first();
        
        $entryDocumentColumn = $possibleEntryDocumentColumn->keys()->first(); // TODO: verify column contain uuid

        $entryProjectColumn = $possibleEntryProjectColumn->keys()->first(); // TODO: verify column contain uuid

        $this->table(['column', 'field'], [
            [$entryIndexColumn, 'index'],
            [$entryDocumentColumn, 'document'],
            [$entryProjectColumn, 'project'],
            ...$possibleFields->map(fn($type, $column) => [$column, is_array($type) ? "{$type['type']->label()}<{$type['concept_collection']?->pref_label}>" : $type->label()])->toArray(),
        ]);

        $confirmed = $this->confirm('Proceed creating the catalog?');

        if(!$confirmed){
            $this->comment('Aborted by user.');
            return ;
        }

        $fieldsToCreate = $possibleFields->except([$entryIndexColumn, $entryDocumentColumn, $entryProjectColumn]);

        if($fieldsToCreate->isEmpty()) {
            $this->error("No fields to create, all columns are empty or only contain a row index.");
            return;
        }

        $this->line("Creating catalog...");
        $catalog = $createCatalog(basename($file), 'Imported from Excel: ' . basename($file), Visibility::PERSONAL, $user);
        
        $this->line("Creating fields...");

        $fields = $fieldsToCreate->mapWithKeys(function($type, $column) use ($createField, $catalog, $user) {

            if(is_array($type)){
                return [$column => $createField($catalog, $column, $type['type'], skosCollection: $type['concept_collection'], user: $user)];
            }

            return [$column => $createField($catalog, $column, $type, user: $user)];
        });

        $this->line("Creating rows...");

        $rows->each(function(array $rowProperties) use ($fields, $entryIndexColumn, $entryDocumentColumn, $entryProjectColumn, $createEntry, $catalog, $user) {

                // column ID or No => entry_index

                $document = null;
                $project = null;

                if(filled($entryDocumentColumn) && Str::isUlid($rowProperties[$entryDocumentColumn])){
                    $document = Document::whereUlid($rowProperties[$entryDocumentColumn])->first();
                }

                if(filled($entryProjectColumn) && Str::isUlid($rowProperties[$entryProjectColumn])){
                    $project = Project::whereUlid($rowProperties[$entryProjectColumn])->first();
                }

                if(is_null($project) && !is_null($document)){
                    $project = $document->project;
                }

                $createEntry(
                    $catalog,
                    [
                        'entry_index' => $rowProperties[$entryIndexColumn] ?? null,
                        // TODO: ensure/validate document accessibility from current user
                        'document_id' => $document?->getKey(),
                        'project_id' => $project?->getKey(),
                        'values' => collect($rowProperties)->except([$entryIndexColumn, $entryProjectColumn, $entryDocumentColumn])->map(function($rowValue, $rowColumn) use ($fields){

                            $field = $fields[$rowColumn];

                            if(blank($rowValue)){
                                return null;
                            }

                            if($field->data_type->isReference()){

                                
                                $conceptValue = $field->skosCollection->concepts()->where('pref_label', $rowValue)->orWhere('notation', $rowValue)->first();
                                
                                if(is_null($conceptValue)){
                                    return null;
                                }

                                return [
                                    'field' => $fields[$rowColumn]->getKey(), // column is SKOS then we need to find the concept ID
                                    'value' => $conceptValue->getKey(),
                                ];
                            }

                            return [
                                'field' => $fields[$rowColumn]->getKey(), // column is SKOS then we need to find the concept ID
                                'value' => $rowValue,
                            ];
                        })->filter()->toArray()
                    ],
                    $user
                );
            });

        $this->comment("Import completed.");
    }
}
