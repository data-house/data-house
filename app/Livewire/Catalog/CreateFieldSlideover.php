<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\Models\Catalog;
use App\CatalogFieldType;
use App\Models\CatalogField;
use App\Livewire\Concern\InteractWithUser;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class CreateFieldSlideover extends SlideoverComponent
{
    use InteractWithUser;

    public $editingForm = [
        'title' => null,
        'description' => null,
        'data_type' => null,
        'constraints' => null,
    ];

    #[Locked]
    public $catalogId;

    public function mount($catalog)
    {
        abort_unless($this->user, 401);

        $this->catalogId = $catalog instanceof Catalog ? $catalog->getKey() : $catalog;
    }

    public function rules() 
    {
        return [
            'editingForm.title' => 'required|string|min:1|max:255',
            'editingForm.description' => 'nullable|string|min:1|max:6000',
            'editingForm.data_type' => ['required', new Enum(CatalogFieldType::class)],
        ];
    }

    public function messages() 
    {
        return [
            'editingForm.title.required' => __('The field name is required.'),
            'editingForm.title.min' => __('The field name must be at least 1 character.'),
            'editingForm.data_type.required' => __('Please select a field type.'),
        ];
    }

    public function storeField(CreateCatalogField $createField)
    {
        $this->validate();

        $catalog = Catalog::findOrFail($this->catalogId);
        
        // Ensure user can add fields to this catalog
        abort_unless($this->user->can('update', $catalog), 403);

        $field = $createField(
            catalog: $catalog,
            title: $this->editingForm['title'],
            fieldType: CatalogFieldType::from($this->editingForm['data_type'] ?? CatalogFieldType::TEXT->value),
            skosCollection: null,
            description: $this->editingForm['description'],
            user: $this->user,
        );

        $this->dispatch('field-created');

        // TODO: think about the best way to refresh the view
        
    }

    
    public function render()
    {
        return view('livewire.catalog.create-field-slideover', [
            'fieldTypes' => CatalogFieldType::cases(),
        ]);
    }
}
