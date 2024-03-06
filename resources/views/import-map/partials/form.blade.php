

<div>
    <x-label for="recursive" value="{{ __('Sub-folders handling') }}" />
    <x-input-error for="recursive" class="mt-2" />
    
    <label for="recursive" class="flex items-center">
        <x-checkbox id="recursive" name="recursive" value="1" :checked="optional($mapping ?? null)->recursive" />
        <span class="ml-2 text-stone-800">{{ __('Recursively import all files in sub-folders') }}</span>
    </label>
</div>
    
<div>
    <x-label for="visibility" value="{{ __('Document visibility') }}" />

    <p>{{ __('Select who should see the imported documents. Leave blank to use the default visibility (:visibility).', ['visibility' => $defaultVisibility->label()]) }}</p>

    <x-input-error for="visibility" class="mt-2" />
    
    @foreach ($availableVisibility as $item)
        <label for="cv-{{$item->name}}" class="w-full px-4 py-2 text-left text-sm leading-5 transition duration-150 ease-in-out block text-stone-700 hover:bg-stone-100 focus:bg-stone-100 focus-within:bg-stone-100">
            <x-radio
                id="cv-{{$item->name}}"
                name="visibility"
                :value="$item->value"
                :checked="optional($mapping ?? null)->visibility === $item"
                />
            {{ $item->label() }}
        </label>
    @endforeach
</div>

<div>
    <x-label for="team" value="{{ __('Target Team') }}" />
    <x-input-error for="team" class="mt-2" />
    
    <select name="team" id="team" class="mt-1 block w-full border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm">
        @foreach ($teams as $team)
            <option value="{{ $team->getKey() }}" @selected(optional($mapping ?? null)->mapped_team === $team->getKey())>{{ $team->name }}</option>
        @endforeach
    </select>
</div>
        
<div>
    <x-label for="description" value="{{ __('Uploader') }}" />
    
    <p>{{ __('Imported documents will appear as uploaded by :name', ['name' => $uploader->name]) }}</p>
</div>


<div>
    <x-label for="schedule" value="{{ __('Schedule') }}" />
    <p>{{ __('Select the frequency to execute the import mapping.') }}</p>

    <x-input-error for="schedule" class="mt-2" />
    
    <select name="schedule" id="schedule" class="mt-1 block w-full border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm">
        @foreach ($availableFrequencies as $frequency)
            <option value="{{ $frequency->value }}" @selected(optional($mapping ?? null)->schedule?->is($frequency))>{{ $frequency->name }}</option>
        @endforeach
    </select>
</div>