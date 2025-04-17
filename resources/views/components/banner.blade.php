@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div x-data="{{ json_encode(['show' => true, 'style' => $style, 'message' => $message]) }}"
        class="sticky top-0 z-50"
            :class="{ 'bg-green-50 border border-green-700 text-green-900': style == 'success', 'border border-red-700 bg-red-50 text-red-900': style == 'danger', 'border border-stone-500 bg-stone-50 text-stone-900': style != 'success' && style != 'danger' }"
            style="display: none;"
            x-show="show && message"
            x-init="
                document.addEventListener('banner-message', event => {
                    style = event.detail.style;
                    message = event.detail.message;
                    show = true;
                });
            ">
    <div class="py-2 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between flex-wrap">
            <div class="w-0 flex-1 flex items-center min-w-0">
                <span class="flex p-2" >
                    <x-heroicon-c-check-circle x-show="style == 'success'" class="w-5 h-5 text-current" />
                    <x-heroicon-c-x-circle x-show="style == 'danger'" class="w-5 h-5 text-current" />
                    <x-heroicon-c-information-circle x-show="style != 'success' && style != 'danger'" class="w-5 h-5 text-current" />
                </span>

                <p class="ml-3 font-medium text-sm truncate" x-text="message"></p>
            </div>

            <div class="shrink-0 sm:ml-3">
                <x-secondary-button
                    type="button"
                    class="-mr-1 "
                    aria-label="{{ __('Dismiss') }}"
                    x-on:click="show = false">
                    <x-heroicon-o-x-mark class="w-5 h-5 text-current" />
                </x-secondary-button>
            </div>
        </div>
    </div>
</div>
