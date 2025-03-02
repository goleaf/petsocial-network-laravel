<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="flex items-center text-gray-600 hover:text-gray-800 focus:outline-none" title="{{ __('common.change_language') }}">
        <x-icons.language class="h-5 w-5 mr-1" stroke-width="1.5" />
        <span class="uppercase">{{ app()->getLocale() }}</span>
        <x-icons.chevron-down class="h-4 w-4 ml-1" />
    </button>
    <div x-show="open" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg z-10">
        <div class="py-1">
            @foreach(config('app.supported_locales') as $locale)
                <a href="{{ route('language.switch', $locale) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center {{ app()->getLocale() === $locale ? 'bg-gray-100' : '' }}" title="{{ __('common.language_'.$locale) }}">
                    <span class="uppercase">{{ $locale }}</span>
                    @if(app()->getLocale() === $locale)
                        <x-icons.check class="h-4 w-4 ml-auto" />
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
