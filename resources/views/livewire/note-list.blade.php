<div class="flex flex-col gap-2">
    @foreach ($this->notes as $note)
        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-md ">
            <livewire:note :note="$note" :key="$note->id" />
        </div>
    @endforeach

    @can('create', \App\Models\Note::class)
        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-md ">
            <livewire:take-note @saved="$refresh" :resource="$resource" />
        </div>    
    @endcan
</div>
