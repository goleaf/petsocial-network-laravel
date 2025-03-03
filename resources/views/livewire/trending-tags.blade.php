<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('common.trending_tags') }}</h2>
    @if ($trendingTags->isEmpty())
        <p class="text-gray-500">{{ __('common.no_trending_tags') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($trendingTags as $tag)
                <li>
                    <a href="{{ route('tag.search') }}?search={{ $tag->name }}" class="text-blue-500 hover:underline">
                        #{{ $tag->name }} ({{ $tag->posts_count }} {{ __('common.posts') }})
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
