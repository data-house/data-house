
<x-slideover  :title="__(':flow execution', ['flow' => $flow->title])" description="" class="max-w-2xl">
    
    <div class="prose mb-2">{{ $flow->description }}</div>

    <p class="mb-6">
       <span class="text-stone-600">{{ __('Catalog:') }}</span> <a href="{{ route('catalogs.show', $catalog) }}" class="underline">{{ $catalog->title }}</a>
    </p>

    <h3 class="font-bold mb-3">{{ __('Runs') }}</h3>

    <table class="w-full text-sm">
        <thead>
            <tr>
                <td class="p-2 w-9/12">{{ __('Run') }}</td>
                <td class="p-2 w-3/12">{{ __('Status') }}</td>
            </tr>
        </thead>
        <tbody>

            @forelse ($runs as $run)
                <tr>
                    <td class="p-2"><span class="inline-block mr-2 font-mono">{{ $run->getKey() }}</span>{{ $run->document->title }}<span class="text-stone-600 block text-sm">{{ $run->user->name }} {{ __('on') }} <x-time :value="$run->created_at" /></span></td>
                    <td class="p-2"><x-status-badge :status="$run->status" /></td>
                </tr>
            @empty
                @if ($document)
                    {{ __('No runs for this flow on :document', ['document' => $document->title]) }}
                @else
                    {{ __('No runs for this flow') }}
                @endif
            @endforelse
        </tbody>
    </table>

    
    
</x-slideover>