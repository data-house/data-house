<div>
    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset

    <div
        x-data="LivewireUISlideover()"
        x-init="init()"
        x-show="isEnabled"
        x-trap="isEnabled"
        x-on:keydown.escape.window="closeSlideoverOnEscape()"
        class="fixed inset-0 z-10 overflow-y-auto"
        style="display: none;"
    >

        @for ($i = 0; $i < count($components) + 1; $i++)
            <!-- Slideover template -->
            <div 
                x-show="isEnabled && visibleComponents.length > {{ $i }}" 
                class="slideover-ui-container fixed inset-0 overflow-hidden z-10"
            >
                <div 
                    x-show="isEnabled && visibleComponents.length > {{ $i }}" 
                    class="slideover-ui-background-overlay absolute inset-0 bg-black bg-opacity-25 z-10"
                    x-on:click="closeSlideoverOnClickAway()"
                    x-transition:enter="ease-in-out duration-150" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="ease-in-out duration-150"
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true" 
                    
                    x-description="Background overlay" 
                ></div>

                @if (count($components) > $i)
                        @php
                            $componentId = collect($components)->slice($i, 1)->keys()->first();
                            $component = $components[$componentId];
                            $key = $componentId;
                            $maxWidth = $component['slideoverAttributes']['maxWidth'] ?? null;
                        @endphp
                
                <div 
                    x-show="isEnabled && visibleComponents.length > {{ $i }}" 
                    {{-- class="" --}}
                    @class([
                        'slideover-ui-panel absolute inset-y-0 right-0 flex bg-white w-full z-10 rounded-lg m-4',
                        'sm:max-w-md md:max-w-xl lg:max-w-3xl xl:max-w-5xl 2xl:max-w-6xl' => $maxWidth && $maxWidth == '6xl',
                        'max-w-2xl' =>  !$maxWidth || ($maxWidth && $maxWidth != '6xl'),
                    ])
                    
                    {{-- x-bind:class="console.log(getComponentAttributeById(getComponentIdByIndex({{ $i }}), 'width'))" --}}
                    
                    x-transition:enter="transform transition ease-in-out duration-150" 
                    x-transition:enter-start="translate-x-full" 
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-150" 
                    x-transition:leave-start="translate-x-0" 
                    x-transition:leave-end="translate-x-full"

                    x-description=" Slideover panel" 
                >
                    
                        
    {{-- @dump($component['slideoverAttributes']) --}}

                        <div x-ref="{{ $key }}" wire:key="{{ $key }}" class="grow">
                            @livewire($component['name'], $component['attributes'], key($key))
                        </div>
                    </div>
                    @endif
            </div>
            {{-- Slideover template --}}
        @endfor
    
    </div>
</div>
