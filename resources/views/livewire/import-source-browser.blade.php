<div class="bg-white min-h-[240px] rounded p-2">
    <div>

    </div>

    <div class="flex flex-col gap-1 max-h-96 min-h-[24rem] relative overflow-y-scroll">

        <div class="flex justify-between gap-4 items-center p-1 sticky top-0 w-full bg-white/90 backdrop-blur">
            {{-- <label for="select-all" class="flex items-center">
                <x-checkbox id="select-all" name="select-all" />
                <span class="ml-2 text-stone-800">{{ __('Select all') }}</span>
            </label> --}}

            <div>
                <p class="font-bold">{{ $location }}</p>
            </div>

            <div>
                {{ __('Showing only compatible files') }}
            </div>
        </div>

        @if (!empty($path))
            <div class="flex gap-4 items-center p-1">
                <button type="button" wire:click="navigateUp()" class="flex gap-2 items-center hover:text-lime-800 ">
                    <x-codicon-fold-up class="text-stone-400 h-5 w-h-5" />
                    {{ __('Back to parent') }}
                </button>
            </div>
        @endif

        @foreach ($directories as $folderKey => $folder)
            <div class="flex gap-4 items-center p-1">
                <label for="folder-{{ $folderKey }}" class="flex items-center">
                    <x-radio id="folder-{{ $folderKey }}" name="selection" value="{{ $folderKey }}" wire:model="selection" />
                    {{--  wire:click="select('{{ $folderKey }}')" --}}
                    <span class="ml-2 text-stone-800 sr-only">{{ __('Select :entry', ['entry' => $folder]) }}</span>
                </label>
                <button type="button" wire:click="navigate('{{$folder}}')" class="flex gap-2 items-center hover:text-lime-800 ">
                    <x-codicon-folder class="text-stone-400 h-5 w-h-5" />
                    {{ $folder }}
                </button>
            </div>
        @endforeach
        @foreach ($files as $fileKey => $file)
            <div class="flex gap-4 items-center p-1">
                <label for="file-{{ $fileKey }}" class="flex items-center">
                    <x-radio id="file-{{ $fileKey }}" name="selection" value="{{ $fileKey }}" wire:model="selection" />
                    <span class="ml-2 text-stone-800 sr-only">{{ __('Select :entry', ['entry' => $file]) }}</span>
                </label>
                <button type="button" class="flex gap-2 items-center">
                    <x-codicon-file class="text-stone-400 h-5 w-h-5" />
                    {{ $file }}
                </button>
            </div>
        @endforeach

    </div>

    <p class="pt-2 flex gap-2">
        {{ __('Selection') }} <span class="font-mono">{{ $selection }}</span>
    </p>
    
    @if ($selection && !empty($selection))
        <input type="hidden" name="paths[]" value="{{ $selection }}">
        
    @endif
</div>
