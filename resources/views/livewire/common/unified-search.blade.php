<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Search Header -->
        <div class="p-6 border-b border-gray-200 space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-4 space-y-6 lg:space-y-0">
                <div class="flex-1 space-y-4">
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icons.search class="h-5 w-5 text-gray-400" stroke-width="2" />
                        </div>
                        <input
                            wire:model.debounce.300ms="query"
                            type="text"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 py-3 sm:text-sm border-gray-300 rounded-md"
                            placeholder="{{ __('search.search_placeholder') }}"
                            autofocus
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icons.location-marker class="h-5 w-5 text-gray-400" stroke-width="2" />
                            </div>
                            <input
                                wire:model.debounce.500ms="location"
                                type="text"
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-4 py-2 sm:text-sm border-gray-300 rounded-md"
                                placeholder="{{ __('search.location_placeholder') }}"
                            >
                        </div>

                        <div class="flex items-center space-x-3">
                            <input
                                wire:model.defer="newSavedSearchName"
                                type="text"
                                class="flex-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full px-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                placeholder="{{ __('search.saved_search_name_placeholder') }}"
                            >
                            <button
                                wire:click="saveCurrentSearch"
                                class="inline-flex items-center px-3 py-2 border border-indigo-500 text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {{ __('search.save_search') }}
                            </button>
                        </div>
                    </div>

                    @error('newSavedSearchName')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="text-xs text-gray-500">
                        {{ __('search.advanced_hint') }}
                    </p>
                </div>

                <div class="flex flex-wrap lg:flex-col lg:items-stretch gap-2">
                    <select
                        wire:model="type"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="all">{{ __('search.filter_all') }}</option>
                        <option value="posts">{{ __('search.filter_posts') }}</option>
                        <option value="users">{{ __('search.filter_users') }}</option>
                        <option value="pets">{{ __('search.filter_pets') }}</option>
                        <option value="tags">{{ __('search.filter_tags') }}</option>
                        <option value="events">{{ __('search.filter_events') }}</option>
                    </select>

                    <select
                        wire:model="filter"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="all">{{ __('search.relationship_all') }}</option>
                        <option value="friends">{{ __('search.relationship_friends') }}</option>
                        <option value="following">{{ __('search.relationship_following') }}</option>
                    </select>

                    <select
                        wire:model="sortField"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="created_at">{{ __('search.sort_newest') }}</option>
                        <option value="name">{{ __('search.sort_name') }}</option>
                        <option value="popularity">{{ __('search.sort_popularity') }}</option>
                    </select>

                    <button
                        wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        @if($sortDirection === 'asc')
                            <x-icons.sort-ascending class="h-4 w-4" />
                        @else
                            <x-icons.sort-descending class="h-4 w-4" />
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:grid lg:grid-cols-4">
            <div class="lg:col-span-3 divide-y divide-gray-200">
                <!-- Search Results -->
                @if(empty($query))
                    <div class="p-10 text-center text-gray-500">
                        <x-icons.search class="h-16 w-16 mx-auto text-gray-400" />
                        <p class="mt-4 text-lg">{{ __('search.enter_search_term') }}</p>
                        <p class="mt-2 text-sm">{{ __('search.empty_state_hint') }}</p>
                    </div>
                @elseif($results['total'] === 0)
                    <div class="p-10 text-center text-gray-500">
                        <x-icons.face-sad class="h-16 w-16 mx-auto text-gray-400" />
                        <p class="mt-4 text-lg">{{ __('search.no_results_found', ['query' => $query]) }}</p>
                        <p class="mt-2 text-sm">{{ __('search.try_adjusting') }}</p>
                    </div>
                @else
                    <!-- Posts Results -->
                    @if(($type === 'all' || $type === 'posts') && $results['posts']->count() > 0)
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">{{ __('search.posts', ['count' => $results['posts']->total()]) }}</h2>
                                <span class="text-sm text-gray-500">{{ __('search.section_hint_posts') }}</span>
                            </div>
                            <div class="space-y-6">
                                @foreach($results['posts'] as $post)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <!-- Post Header -->
                                        <div class="flex items-start space-x-3">
                                            @if($post->pet_id)
                                                <img src="{{ $post->pet->profile_photo_url }}" alt="{{ $post->pet->name }}" class="h-10 w-10 rounded-full">
                                            @else
                                                <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}" class="h-10 w-10 rounded-full">
                                            @endif

                                            <div>
                                                <p class="font-medium">
                                                    @if($post->pet_id)
                                                        {{ $post->pet->name }}
                                                        <span class="text-gray-500 text-sm">({{ $post->user->name }}{{ __('search.pet_owner_suffix') }})</span>
                                                    @else
                                                        {{ $post->user->name }}
                                                    @endif
                                                </p>
                                                <p class="text-gray-500 text-sm">{{ $post->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>

                                        <!-- Post Content -->
                                        <div class="mt-3 space-y-3">
                                            <p class="text-gray-800">{{ $post->content }}</p>

                                            @if($post->photo)
                                                <img src="{{ Storage::url($post->photo) }}" alt="{{ __('search.post_image_alt') }}" class="rounded-lg max-h-64 w-auto">
                                            @endif

                                            @if($post->tags->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($post->tags as $tag)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            #{{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Post Stats -->
                                        <div class="mt-3 flex items-center text-gray-500 text-sm space-x-4">
                                            <span>{{ $post->likes->count() }} {{ __('search.metric_likes') }}</span>
                                            <span>{{ $post->comments->count() }} {{ __('search.metric_comments') }}</span>
                                        </div>
                                    </div>
                                @endforeach

                                @if($type === 'posts')
                                    <div>
                                        {{ $results['posts']->links() }}
                                    </div>
                                @elseif($results['posts'] instanceof \Illuminate\Contracts\Pagination\Paginator && $results['posts']->hasMorePages())
                                    <div class="text-center">
                                        <button wire:click="$set('type', 'posts')" class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('search.view_all_posts') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Users Results -->
                    @if(($type === 'all' || $type === 'users') && $results['users']->count() > 0)
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">{{ __('search.users', ['count' => $results['users']->total()]) }}</h2>
                                <span class="text-sm text-gray-500">{{ __('search.section_hint_users') }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($results['users'] as $user)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm space-y-4">
                                        <div class="flex items-center space-x-4">
                                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full">

                                            <div>
                                                <h3 class="text-lg font-medium">{{ $user->name }}</h3>
                                                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                                                @if($user->location)
                                                    <p class="text-gray-500 text-xs">{{ $user->location }}</p>
                                                @endif
                                                <div class="flex items-center space-x-2 mt-1 text-sm text-gray-500">
                                                    <span>{{ $user->followers->count() }} {{ __('search.metric_followers') }}</span>
                                                    <span>&middot;</span>
                                                    <span>{{ $user->following->count() }} {{ __('search.metric_following') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <a href="{{ route('profile', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                {{ __('search.view_profile') }}
                                            </a>

                                            @if(!auth()->user()->friends->contains($user->id))
                                                <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    {{ __('search.add_friend') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($type === 'users')
                                <div>
                                    {{ $results['users']->links() }}
                                </div>
                            @elseif($results['users'] instanceof \Illuminate\Contracts\Pagination\Paginator && $results['users']->hasMorePages())
                                <div class="mt-6 text-center">
                                    <button wire:click="$set('type', 'users')" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('search.view_all_users') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Pets Results -->
                    @if(($type === 'all' || $type === 'pets') && $results['pets']->count() > 0)
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">{{ __('search.pets', ['count' => $results['pets']->total()]) }}</h2>
                                <span class="text-sm text-gray-500">{{ __('search.section_hint_pets') }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($results['pets'] as $pet)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm space-y-3">
                                        <div class="flex items-center space-x-4">
                                            <img src="{{ $pet->profile_photo_url }}" alt="{{ $pet->name }}" class="h-16 w-16 rounded-full">
                                            <div>
                                                <h3 class="text-lg font-medium">{{ $pet->name }}</h3>
                                                <p class="text-gray-500 text-sm">{{ $pet->type }} &middot; {{ $pet->breed }}</p>
                                                <p class="text-gray-500 text-sm">{{ __('search.pet_owner_prefix') }} {{ $pet->user->name }}</p>
                                                @if($pet->location)
                                                    <p class="text-xs text-gray-500">{{ $pet->location }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($pet->bio)
                                            <p class="text-sm text-gray-600">{{ Str::limit($pet->bio, 100) }}</p>
                                        @endif

                                        <div class="flex justify-between items-center">
                                            <a href="{{ route('pet.posts', $pet->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                {{ __('search.view_pet_profile') }}
                                            </a>

                                            @if($pet->user_id !== auth()->id())
                                                <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    {{ __('search.follow_pet') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($type === 'pets')
                                <div>
                                    {{ $results['pets']->links() }}
                                </div>
                            @elseif($results['pets'] instanceof \Illuminate\Contracts\Pagination\Paginator && $results['pets']->hasMorePages())
                                <div class="mt-6 text-center">
                                    <button wire:click="$set('type', 'pets')" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('search.view_all_pets') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Tags Results -->
                    @if(($type === 'all' || $type === 'tags') && $results['tags']->count() > 0)
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">{{ __('search.tags', ['count' => $results['tags']->total()]) }}</h2>
                                <span class="text-sm text-gray-500">{{ __('search.section_hint_tags') }}</span>
                            </div>
                            <div class="space-y-4">
                                @foreach($results['tags'] as $tag)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium">#{{ $tag->name }}</h3>
                                            <span class="text-gray-500 text-sm">{{ $tag->posts_count ?? $tag->posts->count() }} {{ __('search.metric_posts') }}</span>
                                        </div>

                                        @if($tag->posts->count() > 0)
                                            <div class="space-y-3">
                                                @foreach($tag->posts as $post)
                                                    <div class="border-t border-gray-100 pt-3">
                                                        <div class="flex items-start space-x-3">
                                                            @if($post->pet_id)
                                                                <img src="{{ $post->pet->profile_photo_url }}" alt="{{ $post->pet->name }}" class="h-8 w-8 rounded-full">
                                                            @else
                                                                <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}" class="h-8 w-8 rounded-full">
                                                            @endif
                                                            <div>
                                                                <p class="font-medium text-sm">
                                                                    @if($post->pet_id)
                                                                        {{ $post->pet->name }}
                                                                        <span class="text-gray-500 text-xs">({{ $post->user->name }}{{ __('search.pet_owner_suffix') }})</span>
                                                                    @else
                                                                        {{ $post->user->name }}
                                                                    @endif
                                                                </p>
                                                                <p class="text-gray-800 text-sm mt-1">{{ Str::limit($post->content, 100) }}</p>
                                                                <p class="text-gray-500 text-xs mt-1">{{ $post->created_at->diffForHumans() }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div>
                                            <a href="{{ route('tag.search') }}?query={{ $tag->name }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                {{ __('search.view_tag_posts') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($type === 'tags')
                                <div>
                                    {{ $results['tags']->links() }}
                                </div>
                            @elseif($results['tags'] instanceof \Illuminate\Contracts\Pagination\Paginator && $results['tags']->hasMorePages())
                                <div class="mt-6 text-center">
                                    <button wire:click="$set('type', 'tags')" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('search.view_all_tags') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Events Results -->
                    @if(($type === 'all' || $type === 'events') && $results['events']->count() > 0)
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">{{ __('search.events', ['count' => $results['events']->total()]) }}</h2>
                                <span class="text-sm text-gray-500">{{ __('search.section_hint_events') }}</span>
                            </div>
                            <div class="space-y-4">
                                @foreach($results['events'] as $event)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm space-y-3">
                                        <div class="flex items-start justify-between">
                                            <div class="space-y-1">
                                                <h3 class="text-lg font-medium">{{ $event->title }}</h3>
                                                <p class="text-sm text-gray-500">{{ optional($event->start_date)->toDayDateTimeString() }}</p>
                                                @if($event->location)
                                                    <p class="text-sm text-gray-500 flex items-center space-x-2">
                                                        <x-icons.location-marker class="h-4 w-4 text-gray-400" />
                                                        <span>{{ $event->location }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $event->attendee_count ?? $event->going_count }} {{ __('search.metric_attendees') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">{{ Str::limit($event->description, 140) }}</p>
                                        <div>
                                            <a href="{{ route('group.event', ['group' => $event->group_id, 'event' => $event->id]) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                {{ __('search.view_event') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($type === 'events')
                                <div>
                                    {{ $results['events']->links() }}
                                </div>
                            @elseif($results['events'] instanceof \Illuminate\Contracts\Pagination\Paginator && $results['events']->hasMorePages())
                                <div class="mt-6 text-center">
                                    <button wire:click="$set('type', 'events')" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('search.view_all_events') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <!-- Discovery Sidebar -->
            <div class="border-t border-gray-200 lg:border-l lg:border-t-0 bg-gray-50">
                <div class="p-6 space-y-8">
                    <!-- Search History -->
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('search.search_history') }}</h3>
                            <span class="text-xs text-gray-500">{{ __('search.history_hint') }}</span>
                        </div>
                        <ul class="mt-4 space-y-3">
                            @forelse($searchHistory as $history)
                                <li class="flex items-start justify-between">
                                    <div>
                                        <button
                                            wire:click="rerunSearchFromHistory({{ $history->id }})"
                                            class="text-left text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $history->query }}
                                        </button>
                                        <p class="text-xs text-gray-500">{{ $history->updated_at->diffForHumans() }} · {{ ucfirst($history->search_type) }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $history->results_count }} {{ __('search.metric_results') }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">{{ __('search.no_history') }}</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Saved Searches -->
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('search.saved_searches') }}</h3>
                            <span class="text-xs text-gray-500">{{ __('search.saved_hint') }}</span>
                        </div>
                        <ul class="mt-4 space-y-3">
                            @forelse($savedSearches as $saved)
                                <li class="flex items-center justify-between">
                                    <div>
                                        <button
                                            wire:click="applySavedSearch({{ $saved->id }})"
                                            class="text-left text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $saved->name }}
                                        </button>
                                        <p class="text-xs text-gray-500">{{ __('search.saved_runs', ['count' => $saved->run_count]) }}</p>
                                    </div>
                                    <button
                                        wire:click="deleteSavedSearch({{ $saved->id }})"
                                        class="text-xs text-red-600 hover:text-red-700"
                                    >
                                        {{ __('search.delete_saved_search') }}
                                    </button>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">{{ __('search.no_saved_searches') }}</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Trending Content -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('search.trending_now') }}</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.trending_posts') }}</h4>
                                <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                    @forelse($trendingContent['posts'] ?? [] as $post)
                                        <li class="flex items-center justify-between">
                                            <span>{{ Str::limit($post->content, 40) }}</span>
                                            <span class="text-xs text-gray-400">{{ $post->recent_reactions_count ?? 0 }} {{ __('search.metric_reactions') }}</span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-500">{{ __('search.no_trending_posts') }}</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.trending_tags') }}</h4>
                                <ul class="mt-2 flex flex-wrap gap-2 text-sm">
                                    @forelse($trendingContent['tags'] ?? [] as $tag)
                                        <li class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full">#{{ $tag->name }}</li>
                                    @empty
                                        <li class="text-xs text-gray-500">{{ __('search.no_trending_tags') }}</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.trending_events') }}</h4>
                                <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                    @forelse($trendingContent['events'] ?? [] as $event)
                                        <li class="flex items-center justify-between">
                                            <span>{{ Str::limit($event->title, 40) }}</span>
                                            <span class="text-xs text-gray-400">{{ ($event->attendee_count ?? 0) }} {{ __('search.metric_attendees_short') }}</span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-500">{{ __('search.no_trending_events') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Suggested Content -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('search.suggested_for_you') }}</h3>
                        <div class="mt-4 space-y-4 text-sm text-gray-600">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.suggested_tags') }}</h4>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse(($suggestedContent['tags'] ?? collect()) as $tag)
                                        <a href="{{ route('tag.search') }}?query={{ $tag->name }}" class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200">
                                            #{{ $tag->name }}
                                        </a>
                                    @empty
                                        <span class="text-xs text-gray-500">{{ __('search.no_suggested_tags') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.suggested_users') }}</h4>
                                <ul class="mt-2 space-y-2">
                                    @forelse(($suggestedContent['users'] ?? collect()) as $user)
                                        <li>
                                            <a href="{{ route('profile', $user) }}" class="text-indigo-600 hover:text-indigo-900">{{ $user->name }}</a>
                                            @if($user->location)
                                                <span class="text-xs text-gray-500">· {{ $user->location }}</span>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-500">{{ __('search.no_suggested_users') }}</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700">{{ __('search.suggested_pets') }}</h4>
                                <ul class="mt-2 space-y-2">
                                    @forelse(($suggestedContent['pets'] ?? collect()) as $pet)
                                        <li>
                                            <a href="{{ route('pet.posts', $pet->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $pet->name }}</a>
                                            <span class="text-xs text-gray-500">· {{ $pet->type }}</span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-500">{{ __('search.no_suggested_pets') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
