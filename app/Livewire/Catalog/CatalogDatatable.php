<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
    public ?string $sort_by = null;

    #[Url(as: 'direction', history: true)]
    public ?string $sort_direction = null;
    
    #[Url(as: 's', history: true)]
    public ?string $search = null;

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
        $sorting_field = filled($this->sort_by) ? $this->fields->where('uuid', $this->sort_by)->sole() : null;

        if(filled($this->search)){
            return CatalogEntry::search($this->search)
                ->query(fn (EloquentBuilder $query) => $query->with(['catalogValues.catalogField', 'catalogValues.concept', 'document', 'project']))
                ->where('catalog_id', $this->catalogId)
                ->paginate();

        }


        return $this->catalog->entries()
            ->with(['catalogValues.catalogField', 'catalogValues.concept', 'document', 'project'])
            // sort by entry no ascending if no sorting option defined
            ->when(blank($this->sort_by), function($query){
                $query->orderBy('entry_index', $this->sort_direction === 'asc' ? 'asc' : 'desc');
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
        abort_unless($this->user->can('update', $this->catalog), 403);

        $fields = $this->fields;

        if($index >= $fields->count()){
            return;
        }

        $current = $fields->where('order', $index)->sole();
        $next = $fields
            ->where('order', '>', $index)
            ->where('make_hidden', false)
            ->sortBy('order')
            ->first();

        if (!$next) {
            
            return;
        }

        [$beforeNext, $afterNext] = $fields->partition(function ($f) use ($next) {
            return $f->order <= $next->order;
        });

        $reordered = collect($beforeNext->map->id->diff([$current->id]))
            ->push($current->id)
            ->push($afterNext->map->id)
            ->flatten(1)
            ->values();

        CatalogField::setNewOrder($reordered->all(), modifyQuery: function($query){
            $query->where('catalog_id', $this->catalogId);
        } );

        unset($this->fields);
    }
    
    public function moveFieldLeft(int $index)
    {
        abort_unless($this->user->can('update', $this->catalog), 403);

        $fields = $this->fields;

        if($index <= 1){
            return;
        }

        $current = $fields->where('order', $index)->sole();
        $previous = $fields
            ->where('order', '<', $index)
            ->where('make_hidden', false)
            ->sortByDesc('order')
            ->first();
        
        if (!$previous) {
            return;
        }

        [$beforePrevious, $afterPrevious] = $fields->partition(function ($f) use ($previous) {
            return $f->order < $previous->order;
        });

        $reordered = collect($beforePrevious->map->id)
            ->push($current->id)
            ->push($afterPrevious->map->id->diff([$current->id]))
            ->flatten(1)
            ->values();

        CatalogField::setNewOrder($reordered->all(), modifyQuery: function($query){
            $query->where('catalog_id', $this->catalogId);
        } );

        unset($this->fields);
    }
    
    public function toggleFieldVisibility(int $index)
    {
        abort_unless($this->user->can('update', $this->catalog), 403);

        $fields = $this->fields;

        $current = $fields->where('order', $index)->sole();

        $current->toggleVisibility();

        if($this->sort_by === $current->uuid){
            $this->resetSorting();
        }

        unset($this->fields);
    }

    public function resetSorting($direction = 'asc')
    {
        $this->sort_by = null;
        $this->sort_direction = $direction;
        $this->resetPage();
    }
    
    // Sorting
    public function sortAscending(string $ref)
    {
        $fields = $this->fields;

        // TODO: check if ref is a valid field uuid

        $this->sort_by = $ref;
        $this->sort_direction = 'asc';
        $this->resetPage();
    }
    
    public function sortDescending(string $ref)
    {
        $fields = $this->fields;

        // TODO: check if ref is a valid field uuid

        $this->sort_by = $ref;
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
            'visible_fields' => $this->fields->where('make_hidden', false),
            'all_fields' => $this->fields,
            'entries' => $this->entries,
        ]);
    }
}
