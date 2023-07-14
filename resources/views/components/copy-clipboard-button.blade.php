@props(['value'])

<div x-data="{
    copyText: @js($value),
    copyNotification: false,
    copyToClipboard() {
        $clipboard(this.copyText);
        this.copyNotification = true;
        let that = this;
        setTimeout(function(){
            that.copyNotification = false;
        }, 3000);
    }
    }" {{ $attributes->merge(['class' => 'relative z-20 flex items-center']) }}>
    <div x-show="copyNotification" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-2" class="absolute left-0" x-cloak>
        <div class="px-3 h-7 -ml-1.5 items-center flex text-xs bg-green-500 border-r border-green-500 -translate-x-full text-white rounded">
            <span>{{ __('Copied!') }}</span>
            <div class="absolute right-0 inline-block h-full -mt-px overflow-hidden translate-x-3 -translate-y-2 top-1/2">
                <div class="w-3 h-3 origin-top-left transform rotate-45 bg-green-500 border border-transparent"></div>
            </div>
        </div>
    </div>

    <button  @click="copyToClipboard();" type="button" class="text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
        <span x-show="!copyNotification">
            @isset($icon)
                {{ $icon }}
            @else
                <x-heroicon-m-document-duplicate class="w-5 h-5" />
            @endisset
        </span>
        <x-heroicon-m-check-circle  x-show="copyNotification" class="w-5 h-5 text-green-500" />
        
        <span class="hidden sm:inline">{{ $slot }}</span>
    </button>

</div>



