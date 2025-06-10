<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\CreateCatalogField;
use App\Models\Catalog;
use App\CatalogFieldType;
use App\Http\Requests\RetrievalRequest;
use App\Models\CatalogField;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Document;
use App\Models\Project;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use LivewireUI\Slideover\SlideoverComponent;

class CreateEntrySlideover extends SlideoverComponent implements HasForms
{
    use InteractWithUser;

    use InteractsWithForms;

    public ?array $data = [];


    #[Locked]
    public $catalogId;
    
    #[Locked]
    public $entryId;

    public function mount($catalog, $entry = null)
    {
        abort_unless($this->user, 401);

        $this->catalogId = $catalog instanceof Catalog ? $catalog->getKey() : $catalog;

        abort_if($this->fields()->isEmpty(), 422);

        $this->form->fill();
    }

    #[Computed()]
    public function catalog(): Catalog
    {
        return Catalog::findOrFail($this->catalogId);
    }

    #[Computed()]
    public function fields()
    {
        return $this->catalog->fields()->orderBy('order')->get();
    }

    public function form(Form $form): Form
    {

        $customFormFields = $this->fields
            ->map(fn($field) => $field->toFilamentField());
        
        $baseFields = [
            Select::make('document')
                ->label(__('Document'))
                ->placeholder(__('Select a document'))
                ->searchable()
                ->options(Document::retrieve(RetrievalRequest::fromArray(['sort' => '-date_upload']), user: $this->user)->paginate(6)->pluck('title', 'id')->toArray())
                ->getSearchResultsUsing(fn (string $search): array => Document::retrieve(RetrievalRequest::fromArray(['s' => $search]), user: $this->user)->paginate(6)->pluck('title', 'id')->toArray())
                ->loadingMessage(__('Loading documents...'))
                ->searchPrompt(__('Search documents by title and content'))
                ->searchingMessage(__('Searching documents...')),
            // // Documents and projects are connected, if a document has a project, the project field is inherited
            // Select::make('project')
            //     ->label(__('Project'))
            //     ->placeholder(__('Select a project'))
            //     ->searchable()
            //     ->options(Project::query()->latest('updated_at')->take(6)->pluck('title', 'id')->toArray())
            //     ->getSearchResultsUsing(fn (string $search): array => Project::advancedSearch($search)->paginate(6)->pluck('title', 'id')->toArray())
            //     ->loadingMessage(__('Loading projects...'))
            //     ->searchPrompt(__('Search projects by title and metadata'))
            //     ->searchingMessage(__('Searching projects...')),
        ];

        return $form
            ->schema($customFormFields->merge($baseFields)->all())
            ->statePath('data');
    }


    public function save(CreateCatalogEntry $createEntry)
    {

        // TODO: at least one field must have a value before proceeding
        
        // TODO: additional errors should be catched and handled

        $createEntry = app()->make(CreateCatalogEntry::class); 

        $formState = $this->form->getState();
        
        $values = $this->fields->map(fn($field) => ['field' => $field->id, 'value' => $formState["f_{$field->id}"] ?? null])->all();

        $data = [
            'document_id' => $formState['document'] ?? null,
            'project_id' => $formState['project'] ?? null,
            'values' => $values
        ];

        $createEntry($this->catalog, $data, $this->user);

        $this->dispatch('catalog-entry-added');

        $this->form->fill();
    }

    public function saveAndClose(CreateCatalogEntry $createEntry)
    {
        $this->save($createEntry);

        $this->closeSlideover();
    }

    public function render()
    {
        return view('livewire.catalog.create-entry-slideover', [
            'catalog' => $this->catalog,
            'fields' => $this->fields,
        ]);
    }
}
