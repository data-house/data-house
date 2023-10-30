<?php

namespace App\Livewire;

use App\Actions\ChangeDocumentVisibility;
use App\Models\Document;
use App\Models\Visibility;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DocumentVisibilitySelector extends Component
{
    #[Locked]
    public $document; // TODO: think if can be handled differently like with a computed property or by just using the id

    public $selectedVisibility = null;

    public function mount()
    {
        $this->selectedVisibility = $this->document->visibility->value ?? \App\Models\Visibility::TEAM->value;
    }

    public function save(ChangeDocumentVisibility $changeVisibility)
    {
        $this->authorize('update', $this->document);

        $this->validate([
            'selectedVisibility' => ['required', new Enum(Visibility::class)],
        ]);

        $changeVisibility($this->document, Visibility::from($this->selectedVisibility));
    }

    public function render()
    {
        return view('livewire.document-visibility-selector', [
            'visibility' => $this->document->visibility ?? \App\Models\Visibility::TEAM,
            'options' => Visibility::forDocuments(),
            'team' => $this->document->team?->name,
        ]);
    }
}
