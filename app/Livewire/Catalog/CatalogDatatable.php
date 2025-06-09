<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Livewire\Concern\InteractWithUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Catalog;
use App\Models\CatalogField;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogDatatable extends Component
{
    use InteractWithUser;

    use WithPagination;

    #[Locked]
    public $catalogId;

    #[Url(as: 'sort', history: true)]
    public ?int $sort_by = null;

    #[Url(as: 'direction', history: true)]
    public ?string $sort_direction = null;

    protected $listeners = [
        'field-created' => 'refresh',
    ];

    public function mount(Catalog $catalog)
    {
        abort_unless($this->user, 401);

        $this->catalogId = $catalog->getKey();
    }


    #[Computed()]
    public function catalog(): Catalog
    {
        return Catalog::findOrFail($this->catalogId);
    }


    #[Computed()]
    public function fields()
    {
        return $this->catalog->fields()->ordered()->get();
    }

    #[Computed()]
    public function entries()
    {
        $sorting_field = filled($this->sort_by) ? $this->fields->where('order', $this->sort_by)->sole() : null;

        return $this->catalog->entries()
            ->with(['catalogValues.catalogField', 'catalogValues.concept', 'document', 'project'])
            // sort by entry no ascending if no sorting option defined
            ->when(blank($this->sort_by), function($query){
                $query->orderBy('entry_index', $this->sort_direction === 'desc' ? 'desc' : 'asc');
            })
            ->when(filled($this->sort_by), function($query) use ($sorting_field){

                $value_field = $sorting_field->data_type->valueFieldName();

                $query
                    ->select('catalog_entries.*')
                    ->leftJoin('catalog_values', function($join) use ($sorting_field) {
                        $join->on('catalog_entries.id', '=', 'catalog_values.catalog_entry_id')
                            ->where('catalog_values.catalog_field_id', '=', $sorting_field->id);
                    })
                    ->orderBy("catalog_values.{$value_field}", $this->sort_direction === 'desc' ? 'desc' : 'asc')
                    ;
            })
            ->paginate();
    }


    // Move field order
    public function moveFieldRight(int $index)
    {
        $fields = $this->fields;

        if($index >= $fields->count()){
            return;
        }

        $current = $fields->where('order', $index)->sole();
        $next = $fields->where('order', $index+1)->sole();

        CatalogField::swapOrder($current, $next);

        unset($this->fields);
    }
    
    public function moveFieldLeft(int $index)
    {
        $fields = $this->fields;

        if($index <= 1){
            return;
        }

        $current = $fields->where('order', $index)->sole();
        $previous = $fields->where('order', $index-1)->sole();

        CatalogField::swapOrder($current, $previous);

        unset($this->fields);
    }
    
    // Sorting
    public function sortAscending(int $index)
    {
        $fields = $this->fields;

        if($index < 1){
            $this->sort_by = null;
            $this->sort_direction = 'asc';
            $this->resetPage();
            return;
        }

        if($index >= $fields->count()){
            return;
        }

        $this->sort_by = $index;
        $this->sort_direction = 'asc';
        $this->resetPage();
    }
    
    public function sortDescending(int $index)
    {
        if($index < 1){
            $this->sort_by = null;
            $this->sort_direction = 'desc';
            $this->resetPage();
            return;
        }

        $this->sort_by = $index;
        $this->sort_direction = 'desc';
        $this->resetPage();
    }


    public function generateTodoListExample(CreateCatalogField $createField)
    {
        // Ensure user can add fields to this catalog
        abort_unless($this->user->can('update', $this->catalog), 403);

        $createField(
            catalog: $this->catalog,
            title: __('Activity'),
            fieldType: CatalogFieldType::TEXT,
            description: __('Describe the activity you want to track.'),
            user: $this->user,
        );
        
        $createField(
            catalog: $this->catalog,
            title: __('Completed'),
            fieldType: CatalogFieldType::BOOLEAN,
            description: __('Track whether the activity is completed.'),
            user: $this->user,
        );
        
        $createField(
            catalog: $this->catalog,
            title: __('Due Date'),
            fieldType: CatalogFieldType::DATETIME,
            description: __('Is there a due date for the activity?'),
            user: $this->user,
        );

        $this->dispatch('field-created');
    }


    public function render()
    {
        return view('livewire.catalog.catalog-datatable', [
            'catalog' => $this->catalog,
            'fields' => $this->fields,
            'entries' => $this->entries,
        ]);
    }
}
