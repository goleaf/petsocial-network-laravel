{{-- Lightweight group detail layout used to support HTTP assertions in tests. --}}
<div class="space-y-4">
    {{-- Surface the core metadata so HTTP responses confirm the correct payload renders. --}}
    <header class="space-y-1">
        <h1 class="text-2xl font-semibold">{{ $group->name }}</h1>
        <p class="text-slate-600 dark:text-slate-300">{{ $group->description }}</p>
    </header>

    <section class="grid gap-2">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-slate-500">{{ __('Visibility') }}:</span>
            <span class="text-sm">{{ ucfirst($group->visibility) }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-slate-500">{{ __('Location') }}:</span>
            <span class="text-sm">{{ $group->location ?? __('Not set') }}</span>
        </div>
    </section>
</div>
