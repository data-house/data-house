<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalogField;
use App\CatalogFieldType;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
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
    
    #[Url(as: 'trashed', history: true)]
    public ?bool $trashed = null;

    #[Url(as: 'filters', history: true)]
    public ?array $filters = [];

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
        return $this->catalog->fields()->with('skosCollection.concepts')->ordered()->get();
    }



    #[Computed()]
    public function entries()
    {
        $sorting_field = filled($this->sort_by) ? $this->fields->where('uuid', $this->sort_by)->sole() : null;

        if(filled($this->search) || filled($this->filters)){
            
            $sortField = blank($this->sort_by) ? 'entry_index' : "fields.{$this->sort_by}";

            $sortDirection = $this->sort_direction === 'asc' ? 'asc' : 'desc';

            $builder = CatalogEntry::searchWithCustomFilters($this->search, $this->buildFiltersString())
                ->query(fn (EloquentBuilder $query) => $query->with(['catalogValues.catalogField', 'catalogValues.concept', 'document', 'document.project', 'project']));
            
            if(blank($this->search)){
                $builder->orderBy($sortField, $sortDirection);
            }

            return $builder->paginate();

        }


        return $this->catalog->entries()
            ->when($this->trashed, fn($query) => $query->onlyTrashed())
            ->with(['catalogValues.catalogField', 'catalogValues.concept', 'document', 'document.project', 'project'])
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

    public function applyFilter(string $field, $value)
    {
        
        if(is_null($value)){
            unset($this->filters[$field]);
            return;
        }

        if(filled($this->filters[$field] ?? [])){

            if(!is_array($this->filters[$field])){
                $this->filters[$field] = Arr::wrap($this->filters[$field]);
            }


            if(in_array($value, $this->filters[$field])){
                $this->filters[$field] = Arr::reject($this->filters[$field], function($selectedValue) use ($value){
                    return $selectedValue === $value;
                });
            }
            else {
                $this->filters[$field] = [ ...$this->filters[$field] , $value];
            }
            if(blank($this->filters[$field])){
                $this->clearFilter($field);
            }

            return; 
        }

        $this->filters[$field] = [$value];
    }

    public function clearFilter(string $field)
    {
        unset($this->filters[$field]);
    }
    
    public function clearAllFilters()
    {
        $this->filters = [];
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

        abort_if(blank($fields->where('uuid', $ref)->first()), 404);

        $this->sort_by = $ref;
        $this->sort_direction = 'asc';
        $this->resetPage();
    }
    
    public function sortDescending(string $ref)
    {
        $fields = $this->fields;

        abort_if(blank($fields->where('uuid', $ref)->first()), 404);

        $this->sort_by = $ref;
        $this->sort_direction = 'desc';
        $this->resetPage();
    }


    public function generateTodoListExample(CreateCatalogField $createField)
    {
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

    #[Computed()]
    public function appliedFilters(): array
    {

        $fields = $this->fields->pluck('title', 'uuid');

        return collect($this->filters)
            ->only($fields->keys())
            ->mapWithKeys(function($filterValue, $filterKey) use ($fields){

                if(blank($filterValue)){
                    return null;
                }

                $valueLabel = is_array($filterValue) ? (count($filterValue) > 1 ? trans_choice(':count value|:count values', count($filterValue), ['count' => count($filterValue)]) : $filterValue[0]) : $filterValue;

                return [$filterKey => [
                    'name' => $fields->get($filterKey, 'Not found'),
                    'value' => $valueLabel === '_' ? __('Blank') : $valueLabel,
                ]];
            })
            ->filter()
            ->all();

        // map key to filter name
        // elaborate and make a preview of filter values
    }

    protected function buildFiltersString(): string
    {

        $fields = $this->fields->pluck('data_type', 'uuid');

        $validatedFilters = Arr::only($this->filters, $fields->keys()->all());

        $catalogTenantFilter = "catalog_id = {$this->catalogId}";

        $userFilters = collect($validatedFilters)->map(function($value, $key) use ($fields){

            if(is_array($value)){

                if(in_array('_', $value)){

                    $vals = collect($value)->map(function ($value) {
                        if($value === '_'){
                            return null;
                        }

                        if (is_bool($value)) {
                            return sprintf('%s', $value ? 'true' : 'false');
                        }

                        return filter_var($value, FILTER_VALIDATE_INT) !== false
                            ? sprintf('%s', $value)
                            : sprintf('"%s"', $value);
                    })->filter()->values();

                    if($vals->isEmpty()){
                        return "(fields.{$key} IS NULL OR NOT fields.{$key} EXISTS)";
                    }

                    $otherWheres = sprintf('%s %s [%s]', "fields.{$key}", 'IN', $vals->implode(', '));

                    return "(fields.{$key} IS NULL OR NOT fields.{$key} EXISTS OR {$otherWheres})";
                }
                
                return sprintf('%s %s [%s]', "fields.{$key}", 'IN', collect($value)->map(function ($value) {
                    if (is_bool($value)) {
                        return sprintf('%s', $value ? 'true' : 'false');
                    }

                    return filter_var($value, FILTER_VALIDATE_INT) !== false
                        ? sprintf('%s', $value)
                        : sprintf('"%s"', $value);
                })->values()->implode(', '));
            

            }
            else {

                $fieldType = $fields->get($key, CatalogFieldType::TEXT);

                
                if (is_bool($value)) {
                    return sprintf('%s=%s', "fields.{$key}", $value ? 'true' : 'false');
                }
                
                if (is_null($value)) {
                    return sprintf('%s %s', "fields.{$key}", 'IS NULL');
                }
                
                if($fieldType == CatalogFieldType::TEXT || $fieldType == CatalogFieldType::MULTILINE_TEXT){
                    // if text field we use the contains operator https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#contains
                    return sprintf('%s CONTAINS "%s"', "fields.{$key}", $value);
                }

                return is_numeric($value)
                    ? sprintf('%s=%s', "fields.{$key}", $value)
                    : sprintf('%s="%s"', "fields.{$key}", $value);
            }
        })->filter()->join(' AND ');

        if(filled($userFilters)){
            return "{$catalogTenantFilter} AND {$userFilters}";
        }

        return "{$catalogTenantFilter}";
    }


    public function render()
    {
        return view('livewire.catalog.catalog-datatable', [
            'catalog' => $this->catalog,
            'visible_fields' => $this->fields->where('make_hidden', false),
            'all_fields' => $this->fields,
            'entries' => $this->entries,
            'applied_filters' => $this->appliedFilters,
        ]);
    }
}
