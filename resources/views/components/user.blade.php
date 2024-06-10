@props(['user'])

<span class="inline-flex items-center gap-1">
    <x-heroicon-o-user class="w-6 h-6 text-stone-600" />
    
    {{ $user->name }}
</span>