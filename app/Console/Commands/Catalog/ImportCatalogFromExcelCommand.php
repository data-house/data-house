<?php

namespace App\Console\Commands\Catalog;

use App\Actions\Catalog\CreateCatalog;
use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Models\User;
use App\Models\Visibility;
use DateTime;
use DateTimeImmutable;
use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelReader;

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
    public function handle(CreateCatalog $createCatalog, CreateCatalogField $createField): void
    {
        // Get the first rows of the file to establish the structure and the datatype of the columns

        // Read the whole file


        $file = $this->argument('file');

        $user = User::find(1);

        if(file_exists($file) === false) {
            $this->error("File does not exist: {$file}");
            return;
        }


        $catalog = $createCatalog(basename($file), 'This catalog was imported from Excel file: ' . basename($file), Visibility::PERSONAL, $user);
        $this->info("Catalog created: {$catalog->title} ({$catalog->getKey()})");

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

            if($values->filter(fn($value) => is_string($value))->map(fn($value) => strlen($value))->max() <= 100) {
                return [$column => CatalogFieldType::TEXT];
            }


            return [$column => CatalogFieldType::MULTILINE_TEXT];
        });


        $possibleEntryIndexColumn = $possibleFields->only(['No', 'ID', 'Index', 'Entry No','no', 'id', 'index', 'entry no']);

        if($possibleEntryIndexColumn->isNotEmpty() && $possibleEntryIndexColumn->count() > 1) {
            $this->error("Ambiguous index column, found {$possibleEntryIndexColumn->count()} candidates: {$possibleEntryIndexColumn->join(',')}");
            return;
        }

        $entryIndexColumn = dump($possibleEntryIndexColumn->keys()->first());


        $fieldsToCreate = $possibleFields->except($entryIndexColumn)->dump();

        if($fieldsToCreate->isEmpty()) {
            $this->error("No fields to create, all columns are empty or only contain a row index.");
            return;
        }
        

        $fields = $fieldsToCreate->map(function($type, $column) use ($createField, $catalog, $user) {
            return $createField($catalog, $column, $type, user: $user);
        });

        // SimpleExcelReader::create($file)
        //     ->trimHeaderRow()
        //     ->getRows()
        //     ->each(function(array $rowProperties) {

        //         // column ID or No => entry_index

        //         dd($rowProperties);
        //     });
    }
}
