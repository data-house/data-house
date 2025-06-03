<?php

namespace App\Livewire\Catalog;

use App\Livewire\Concern\InteractWithUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Catalog;
use App\Models\CatalogField;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogDatatable extends Component
{
    use InteractWithUser;

    use WithPagination;

    #[Locked]
    public $catalogId;

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

        return $this->catalog->fields()->orderBy('order')->get();
    }

    #[Computed()]
    public function entries()
    {

        return $this->catalog->entries()->with(['catalogValues.catalogField'])->paginate();
    }


    public function fieldsSorted(array $items)
    {
        $currentOrder = $this->fields->pluck('id', 'order');
        
        $order = collect($items)->pluck('value')->toArray();

        $movedFields = $currentOrder->values()->diffAssoc($order);

        dd(compact('currentOrder', 'order', 'movedFields'));

        // CatalogField::setNewOrder(ids: $order, modifyQuery: function(Builder $query){
        //     $query->where('catalog_id', $this->catalog->getKey());
        // });

        // unset($this->days);
        // $movedDays->each(fn($day) => $this->dispatch("day-{$day}-updated"));
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
