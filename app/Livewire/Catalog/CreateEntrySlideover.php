<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\CreateCatalogField;
use App\Models\Catalog;
use App\CatalogFieldType;
use App\Models\CatalogField;
use App\Livewire\Concern\InteractWithUser;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use LivewireUI\Slideover\SlideoverComponent;

class CreateEntrySlideover extends SlideoverComponent implements HasForms
{
    use InteractWithUser;

    use InteractsWithForms;

    public ?array $data = [];


    #[Locked]
    public $catalogId;

    public function mount($catalog)
    {
        abort_unless($this->user, 401);

        $this->catalogId = $catalog instanceof Catalog ? $catalog->getKey() : $catalog;

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
        

        return $form
            ->schema(
                $this->fields->map(fn($field) => $field->toFilamentField())->all())
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
            'document_id' => null,
            'project_id' => null,
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
