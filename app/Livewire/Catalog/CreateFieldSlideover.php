<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\Models\Catalog;
use App\CatalogFieldType;
use App\Models\CatalogField;
use App\Livewire\Concern\InteractWithUser;
use App\Models\SkosCollection;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use Filament\Forms\Form;
use Filament\Forms\Get;
use LivewireUI\Slideover\SlideoverComponent;

class CreateFieldSlideover extends SlideoverComponent implements HasForms
{
    use InteractWithUser;

    use InteractsWithForms;

    public $editingForm = [
        'title' => null,
        'description' => null,
    ];

    public ?array $data = [];

    #[Locked]
    public $catalogId;

    public function mount($catalog)
    {
        abort_unless($this->user, 401);

        $this->catalogId = $catalog instanceof Catalog ? $catalog->getKey() : $catalog;

        $this->form->fill();
    }

    public function form(Form $form): Form
    {

        $fields = [
            Radio::make('data_type')
                ->label(__("Field type"))
                ->options(CatalogFieldType::allLabels())
                ->descriptions(CatalogFieldType::allDescriptions())
                ->enum(CatalogFieldType::class)
                ->required()
                ->validationMessages([
                    'required' => 'Select a type for the field.',
                ])
                ->live(),
            Select::make('concept_collection')
                ->visible(fn (Get $get): bool => (int)$get('data_type') === CatalogFieldType::SKOS_CONCEPT->value)
                ->requiredIf('data_type', CatalogFieldType::SKOS_CONCEPT->name)
                ->exists(table: SkosCollection::class, column: 'id')
                ->label(__('Vocabulary concept group'))
                ->placeholder(__('Select the concept group providing the acceptable values...'))
                ->searchable()
                ->preload()
                ->native(false)
                ->optionsLimit(10)
                ->options(SkosCollection::query()->latest()->paginate(6)->pluck('pref_label', 'id'))
                ->getSearchResultsUsing(fn (string $search) => SkosCollection::query()->where('pref_label', 'like', '%'.e($search).'%')->latest()->paginate(6)->pluck('pref_label', 'id'))
                ->loadingMessage(__('Loading vocabulary groups...'))
                ->searchPrompt(__('Search vocabulary groups by title and content'))
                ->searchingMessage(__('Searching vocabulary groups...')),
        ];

        return $form
            ->schema($fields)
            ->statePath('data');
    }

    public function rules() 
    {
        return [
            'editingForm.title' => 'required|string|min:1|max:255',
            'editingForm.description' => 'nullable|string|min:1|max:6000',
        ];
    }

    public function messages() 
    {
        return [
            'editingForm.title.required' => __('The field name is required.'),
            'editingForm.title.min' => __('The field name must be at least 1 character.'),
        ];
    }

    public function storeField(CreateCatalogField $createField)
    {
        $this->validate();

        $formState = $this->form->getState();

        $catalog = Catalog::findOrFail($this->catalogId);
        
        // Ensure user can add fields to this catalog
        abort_unless($this->user->can('update', $catalog), 403);

        $field = $createField(
            catalog: $catalog,
            title: $this->editingForm['title'],
            fieldType: $formState['data_type'] instanceof CatalogFieldType ? $formState['data_type'] : CatalogFieldType::from($formState['data_type']),
            skosCollection: $formState['concept_collection'] ?? false ? SkosCollection::find($formState['concept_collection']) : null,
            description: $this->editingForm['description'],
            user: $this->user,
        );

        $this->dispatch('field-created');

        $this->closeSlideover();
        
    }

    
    public function render()
    {
        return view('livewire.catalog.create-field-slideover', [
            'fieldTypes' => CatalogFieldType::cases(),
        ]);
    }
}
