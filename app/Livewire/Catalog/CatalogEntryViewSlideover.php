<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalog;
use App\Actions\Catalog\Flow\ExecuteCatalogFieldFlow;
use App\Actions\Catalog\RestoreCatalogEntry;
use App\Actions\Review\RequestQuestionReview;
use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\Question;
use App\Models\Team;
use App\Models\Visibility;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class CatalogEntryViewSlideover extends SlideoverComponent
{

    use InteractWithUser;


    #[Locked]
    public $catalogEntryId;


    public function mount($catalogEntry)
    {
        abort_unless($this->user, 401);

        $catalogEntry = $catalogEntry instanceof CatalogEntry ? $catalogEntry : CatalogEntry::withTrashed()->findOrFail($catalogEntry);
        
        $this->user->can('view', $catalogEntry);

        $this->catalogEntryId = $catalogEntry->getKey();

    }

    #[Computed()]
    public function entry()
    {
        return CatalogEntry::withTrashed()->find($this->catalogEntryId)
            ->load([
                'user',
                'lastUpdatedBy',
                'catalog',
                'catalogValues.catalogField',
                'catalogValues.concept',
                'document',
                'project',
            ]);
    }

    #[Computed()]
    public function fields()
    {
        return $this->entry->catalog->fields()->with('flows')->ordered()->get();
    }

    public static function slideoverWidth(): string
    {
        return 'w-full';
    }

    public static function slideoverMaxWidth(): string
    {
        return '6xl';
    }

    public function restoreEntry()
    {
        $restoreEntry = app()->make(RestoreCatalogEntry::class);

        $restoreEntry($this->entry, $this->user);

        $this->dispatch('banner-message', 
            type: 'success',
            message: __('Entry :entry restored from trash.', ['entry' => $this->entry->entry_index]),
        );

        $this->dispatch('catalog-entry-added');
    }


    public function triggerFlow($flowUuid)
    {
        $flows = $this->fields->pluck('flows')->flatten()->mapWithKeys(fn($f) => [$f->uuid => $f]);

        $flow = $flows->get($flowUuid);

        if(blank($flow)){
            throw ValidationException::withMessages(['action' => __('Please select a valid action')]);
        }

        $executeFlow = app()->make(ExecuteCatalogFieldFlow::class);

        $executeFlow($flow, $this->entry, user: $this->user);

        unset($this->entry);
    }

    
    public function render()
    {
        return view('livewire.catalog.catalog-entry-view-slideover', [
            'catalog_entry' => $this->entry,
            'fields' => $this->fields,
        ]);
    }
}
