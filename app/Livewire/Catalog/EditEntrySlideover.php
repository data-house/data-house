<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\DeleteCatalogEntry;
use App\Actions\Catalog\PermanentDeleteCatalogEntry;
use App\Actions\Catalog\RestoreCatalogEntry;
use App\Actions\Catalog\UpdateCatalogEntry;
use App\Models\Catalog;
use App\Http\Requests\RetrievalRequest;
use App\Livewire\Concern\InteractWithUser;
use App\Models\CatalogEntry;
use App\Models\Document;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use LivewireUI\Slideover\SlideoverComponent;

class EditEntrySlideover extends SlideoverComponent implements HasForms
{
    use InteractWithUser;

    use InteractsWithForms;

    public ?array $data = [];
    
    #[Locked]
    public $entryId;

    public function mount($entry)
    {
        abort_unless($this->user, 401);

        $this->entryId = $entry instanceof CatalogEntry ? $entry->getKey() : $entry;

        abort_if($this->fields()->isEmpty(), 422);

        $vals = $this->fieldValues->mapWithKeys(fn($val) => $val->toFilamentFieldValue());

        $this->form->fill([
            ...$vals,
            'document' => $this->entry->document_id,
        ]);
    }

    #[Computed()]
    public function entry(): CatalogEntry
    {
        return CatalogEntry::withTrashed()->findOrFail($this->entryId)->load(['catalog', 'catalogValues', 'catalogValues.catalogField', 'document']);
    }

    #[Computed()]
    public function catalog(): Catalog
    {
        return $this->entry->catalog;
    }

    #[Computed()]
    public function fields()
    {
        return $this->catalog->fields()->orderBy('order')->get();
    }
    
    #[Computed()]
    public function fieldValues()
    {
        return $this->entry->catalogValues;
    }

    public function form(Form $form): Form
    {
        $customFormFields = $this->fields
            ->map(fn($field) => $field->toFilamentField());

        $documentBaseOptions = collect($this->entry->document ? [$this->entry->document->getKey() => $this->entry->document->title] : [])
            ->concat(Document::retrieve(RetrievalRequest::fromArray(['sort' => '-date_upload']), user: $this->user)
                        ->paginate(6)
                        ->pluck('title', 'id'))->unique()->all();
   
        $baseFields = [
            Select::make('document')
                ->label(__('Document'))
                ->placeholder(__('Select a document'))
                ->searchable()
                ->options($documentBaseOptions)
                ->getSearchResultsUsing(fn (string $search): array => Document::retrieve(RetrievalRequest::fromArray(['s' => $search]), user: $this->user)->paginate(6)->pluck('title', 'id')->toArray())
                ->loadingMessage(__('Loading documents...'))
                ->searchPrompt(__('Search documents by title and content'))
                ->searchingMessage(__('Searching documents...')),
        ];

        return $form
            ->schema($customFormFields->merge($baseFields)->all())
            ->statePath('data');
    }


    public function save()
    {
        $updateEntry = app()->make(UpdateCatalogEntry::class); 

        $formState = $this->form->getState();
        
        $values = $this->fields->map(fn($field) => ['field' => $field->id, 'value' => $formState["f_{$field->id}"] ?? null])->all();

        $data = [
            'document_id' => $formState['document'] ?? null,
            'project_id' => $formState['project'] ?? null,
            'values' => $values
        ];

        $updateEntry($this->entry, $this->catalog, $data, $this->user);

        $this->dispatch('catalog-entry-added');

        $this->closeSlideover();
    }

    public function trash()
    {
        $trashEntry = app()->make(DeleteCatalogEntry::class); 

        $trashEntry($this->entry, $this->user);

        $this->dispatch('banner-message', 
            type: 'success',
            message: __('Entry :entry trashed. You can still recover it from the trash if needed.', ['entry' => $this->entry->entry_index]),
        );

        $this->closeSlideover();

        $this->dispatch('catalog-entry-added');
    }
    
    public function restoreEntry()
    {
        $restoreEntry = app()->make(RestoreCatalogEntry::class); 

        $restoreEntry($this->entry, $this->user);

        $this->dispatch('banner-message', 
            type: 'success',
            message: __('Entry :entry restored from trash.', ['entry' => $this->entry->entry_index]),
        );

        $this->closeSlideover();

        $this->dispatch('catalog-entry-added');
    }
    
    public function forceDelete()
    {
        $deleteEntry = app()->make(PermanentDeleteCatalogEntry::class); 

        $deleteEntry($this->entry, $this->user);

        $this->dispatch('banner-message', 
            type: 'success',
            message: __('Entry :entry deleted.', ['entry' => $this->entry->entry_index]),
        );

        $this->closeSlideover();

        $this->dispatch('catalog-entry-added');
    }

    public function render()
    {
        return view('livewire.catalog.edit-entry-slideover', [
            'catalog' => $this->catalog,
            'fields' => $this->fields,
            'entry' => $this->entry,
        ]);
    }
}
