<?php

namespace App\Http\Livewire\Common;

use App\Models\Group\Event;
use App\Models\Pet;
use App\Models\Post;
use App\Models\SavedSearch;
use App\Models\SearchHistory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class UnifiedSearch
 *
 * Delivers the advanced discovery experience that merges global search, saved searches, history,
 * trending highlights, and personalised suggestions across posts, users, pets, tags, and events.
 */
class UnifiedSearch extends Component
{
    use WithPagination;

    /**
     * The raw search query entered by the current user.
     *
     * @var string
     */
    public $query = '';

    /**
     * Selected resource scope for the search request.
     *
     * @var string
     */
    public $type = 'all'; // all, posts, users, pets, tags, events

    /**
     * Relationship filter applied to the results list.
     *
     * @var string
     */
    public $filter = 'all'; // all, friends, following

    /**
     * Field used when sorting datasets that expose the metadata.
     *
     * @var string
     */
    public $sortField = 'created_at';

    /**
     * Direction for the selected sort field.
     *
     * @var string
     */
    public $sortDirection = 'desc';

    /**
     * Pagination size for each section when a dedicated tab is opened.
     *
     * @var int
     */
    public $perPage = 10;

    /**
     * Optional free-form location filter for location-based discovery.
     *
     * @var string
     */
    public $location = '';

    /**
     * Collection of recent searches to present in the sidebar.
     *
     * @var Collection<int, SearchHistory>
     */
    public $searchHistory;

    /**
     * Collection of saved searches owned by the current member.
     *
     * @var Collection<int, SavedSearch>
     */
    public $savedSearches;

    /**
     * Aggregated trending data grouped by resource type.
     *
     * @var array<string, Collection>
     */
    public $trendingContent = [];

    /**
     * Personalised suggestions assembled from history and relationships.
     *
     * @var array<string, Collection>
     */
    public $suggestedContent = [];

    /**
     * Temporary property used to capture the new saved search label.
     *
     * @var string
     */
    public $newSavedSearchName = '';

    /**
     * Track whether the search history entry has been persisted during the lifecycle.
     */
    protected bool $historyRecorded = false;

    protected $queryString = [
        'query' => ['except' => ''],
        'type' => ['except' => 'all'],
        'filter' => ['except' => 'all'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'location' => ['except' => ''],
    ];
    
    protected $paginationTheme = 'tailwind';
    
    protected $listeners = [
        'refreshSearch' => '$refresh',
    ];
    
    /**
     * Initialise the component state and preload sidebar data collections.
     */
    public function mount($initialQuery = '', $initialType = 'all')
    {
        $this->query = $initialQuery;
        $this->type = $initialType;
        $this->searchHistory = collect();
        $this->savedSearches = collect();

        $this->refreshSidebarData();
    }

    /**
     * Refresh non-paginated datasets whenever the component rehydrates.
     */
    public function hydrate(): void
    {
        $this->refreshSidebarData(false);
    }
    
    public function updatedQuery()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedType()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedFilter()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedSortField()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedSortDirection()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }

    /**
     * React to location filter updates and reset pagination accordingly.
     */
    public function updatedLocation(): void
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    protected function clearSearchCache()
    {
        $types = ['all', 'posts', 'users', 'pets', 'tags', 'events'];
        $filters = ['all', 'friends', 'following'];
        $sortFields = ['created_at', 'name', 'popularity'];
        $sortDirections = ['asc', 'desc'];

        // Clear cache combinations that might be affected by the current state.
        foreach ($types as $type) {
            foreach ($filters as $filter) {
                foreach ($sortFields as $field) {
                    foreach ($sortDirections as $direction) {
                        Cache::forget($this->buildCacheKey($type, $filter, $field, $direction, $this->location, $this->query));
                    }
                }
            }
        }
    }

    protected function getSearchResults()
    {
        // If the query is empty and the user has not selected a specific scope, bail early.
        if (empty($this->query) && $this->type === 'all') {
            return [
                'posts' => collect(),
                'users' => collect(),
                'pets' => collect(),
                'tags' => collect(),
                'events' => collect(),
                'total' => 0,
            ];
        }

        $segments = $this->resolveQuerySegments();
        $activeType = $this->determineActiveType($segments);
        $locationFilter = $segments['operators']['location'] ?? $this->location;

        if (!empty($segments['operators']['location']) && $this->location !== $locationFilter) {
            $this->location = $locationFilter;
        }

        $cacheKey = $this->buildCacheKey($activeType, $this->filter, $this->sortField, $this->sortDirection, $locationFilter, $this->query);

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($activeType, $locationFilter, $segments) {
            $results = [
                'posts' => collect(),
                'users' => collect(),
                'pets' => collect(),
                'tags' => collect(),
                'events' => collect(),
            ];

            $total = 0;
            $user = auth()->user();

            if (!$user) {
                return array_merge($results, ['total' => $total]);
            }

            $blockedIds = $user->blocks ? $user->blocks->pluck('id')->toArray() : [];
            $friendIds = $user->getFriendIds();
            $followingIds = $user->following()->pluck('follows.followed_id')->toArray();

            $searchTokens = $this->prepareSearchTokens($segments);

            if ($activeType === 'all' || $activeType === 'posts') {
                $postsQuery = Post::query()
                    ->whereNotIn('user_id', $blockedIds)
                    ->where(function ($query) use ($searchTokens, $segments) {
                        $this->applySearchTokenConstraints($query, $searchTokens, $segments['tags']);
                    });

                $postsQuery->where(function ($query) use ($friendIds) {
                    $query->where('posts_visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('posts_visibility', 'friends')
                                ->whereIn('user_id', $friendIds);
                        })
                        ->orWhere('user_id', auth()->id());
                });

                if (!empty($locationFilter)) {
                    $postsQuery->where(function ($query) use ($locationFilter) {
                        $query->whereHas('user', function ($userQuery) use ($locationFilter) {
                            $userQuery->where('location', 'like', '%' . $locationFilter . '%');
                        })->orWhereHas('pet', function ($petQuery) use ($locationFilter) {
                            $petQuery->where('location', 'like', '%' . $locationFilter . '%');
                        });
                    });
                }

                if ($this->filter === 'friends') {
                    $postsQuery->whereIn('user_id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $postsQuery->whereIn('user_id', $followingIds);
                }

                if ($this->sortField === 'popularity') {
                    $postsQuery->withCount(['reactions as reactions_count'])->orderBy('reactions_count', $this->sortDirection);
                } else {
                    $postsQuery->orderBy($this->sortField, $this->sortDirection);
                }

                $postsQuery->with(['user', 'pet', 'tags', 'reactions']);
                $posts = $postsQuery->paginate($this->perPage, ['*'], 'postsPage');
                $results['posts'] = $posts;
                $total += $posts instanceof LengthAwarePaginator ? $posts->total() : $posts->count();
            }

            if ($activeType === 'all' || $activeType === 'users') {
                $usersQuery = User::query()
                    ->whereNotIn('id', $blockedIds)
                    ->where('id', '!=', auth()->id())
                    ->where(function ($query) use ($searchTokens, $segments) {
                        $this->applyUserSearchTokenConstraints($query, $searchTokens, $segments['tags']);
                    });

                $usersQuery->where(function ($query) use ($friendIds) {
                    $query->where('profile_visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('profile_visibility', 'friends')
                                ->whereIn('id', $friendIds);
                        });
                });

                if (!empty($locationFilter)) {
                    $usersQuery->where(function ($query) use ($locationFilter) {
                        $query->where('location', 'like', '%' . $locationFilter . '%');
                    });
                }

                if ($this->filter === 'friends') {
                    $usersQuery->whereIn('id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $usersQuery->whereIn('id', $followingIds);
                }

                if ($this->sortField === 'popularity') {
                    $usersQuery->withCount('followers')->orderBy('followers_count', $this->sortDirection);
                } else {
                    $usersQuery->orderBy($this->sortField === 'created_at' ? 'created_at' : 'name', $this->sortDirection);
                }

                $usersQuery->with(['posts' => function ($q) {
                    $q->latest()->limit(3);
                }, 'followers', 'following']);

                $users = $usersQuery->paginate($this->perPage, ['*'], 'usersPage');
                $results['users'] = $users;
                $total += $users instanceof LengthAwarePaginator ? $users->total() : $users->count();
            }

            if ($activeType === 'all' || $activeType === 'pets') {
                $petsQuery = Pet::query()
                    ->whereNotIn('user_id', $blockedIds)
                    ->where(function ($query) use ($searchTokens) {
                        $first = true;
                        foreach ($searchTokens as $token) {
                            if ($first) {
                                $query->where('name', 'like', '%' . $token . '%');
                                $first = false;
                            } else {
                                $query->orWhere('name', 'like', '%' . $token . '%');
                            }
                            $query->orWhere('type', 'like', '%' . $token . '%')
                                ->orWhere('breed', 'like', '%' . $token . '%')
                                ->orWhere('bio', 'like', '%' . $token . '%');
                        }

                        if ($first) {
                            $query->where('name', 'like', '%' . $this->query . '%')
                                ->orWhere('type', 'like', '%' . $this->query . '%')
                                ->orWhere('breed', 'like', '%' . $this->query . '%')
                                ->orWhere('bio', 'like', '%' . $this->query . '%');
                        }
                    });

                $petsQuery->where(function ($query) use ($friendIds) {
                    $query->where('visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('visibility', 'friends')
                                ->whereIn('user_id', $friendIds);
                        })
                        ->orWhere('user_id', auth()->id());
                });

                if (!empty($locationFilter)) {
                    $petsQuery->where('location', 'like', '%' . $locationFilter . '%');
                }

                if ($this->filter === 'friends') {
                    $petsQuery->whereIn('user_id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $petsQuery->whereIn('user_id', $followingIds);
                }

                if ($this->sortField === 'popularity') {
                    $petsQuery->withCount('followers')->orderBy('followers_count', $this->sortDirection);
                } else {
                    $petsQuery->orderBy($this->sortField === 'name' ? 'name' : 'created_at', $this->sortDirection);
                }

                $petsQuery->with(['user', 'posts' => function ($q) {
                    $q->latest()->limit(3);
                }]);

                $pets = $petsQuery->paginate($this->perPage, ['*'], 'petsPage');
                $results['pets'] = $pets;
                $total += $pets instanceof LengthAwarePaginator ? $pets->total() : $pets->count();
            }

            if ($activeType === 'all' || $activeType === 'tags') {
                $tagsQuery = Tag::query();

                $tagsQuery->where(function ($query) use ($segments) {
                    $first = true;
                    foreach ($segments['tags'] as $tagName) {
                        if ($first) {
                            $query->where('name', 'like', '%' . $tagName . '%');
                            $first = false;
                        } else {
                            $query->orWhere('name', 'like', '%' . $tagName . '%');
                        }
                    }

                    if ($first) {
                        $query->where('name', 'like', '%' . $this->query . '%');
                    }
                });

                if ($this->sortField === 'popularity') {
                    $tagsQuery->withCount('posts')->orderBy('posts_count', $this->sortDirection);
                } else {
                    $tagsQuery->orderBy('name', $this->sortDirection);
                }

                $tagsQuery->with(['posts' => function ($q) use ($blockedIds, $friendIds) {
                    $q->whereNotIn('user_id', $blockedIds)
                        ->where(function ($query) use ($friendIds) {
                            $query->where('posts_visibility', 'public')
                                ->orWhere(function ($q) use ($friendIds) {
                                    $q->where('posts_visibility', 'friends')
                                        ->whereIn('user_id', $friendIds);
                                })
                                ->orWhere('user_id', auth()->id());
                        })
                        ->latest()
                        ->limit(5);
                }]);

                $tags = $tagsQuery->paginate($this->perPage, ['*'], 'tagsPage');
                $results['tags'] = $tags;
                $total += $tags instanceof LengthAwarePaginator ? $tags->total() : $tags->count();
            }

            if ($activeType === 'all' || $activeType === 'events') {
                $eventsQuery = Event::query()
                    ->where('is_published', true)
                    ->where(function ($query) use ($searchTokens) {
                        $first = true;
                        foreach ($searchTokens as $token) {
                            if ($first) {
                                $query->where('title', 'like', '%' . $token . '%');
                                $first = false;
                            } else {
                                $query->orWhere('title', 'like', '%' . $token . '%');
                            }
                            $query->orWhere('description', 'like', '%' . $token . '%');
                        }

                        if ($first) {
                            $query->where('title', 'like', '%' . $this->query . '%')
                                ->orWhere('description', 'like', '%' . $this->query . '%');
                        }
                    });

                if (!empty($locationFilter)) {
                    $eventsQuery->where(function ($query) use ($locationFilter) {
                        $query->where('location', 'like', '%' . $locationFilter . '%')
                            ->orWhere('location_url', 'like', '%' . $locationFilter . '%');
                    });
                }

                if ($this->sortField === 'popularity') {
                    $eventsQuery->withCount(['going as attendee_count'])->orderBy('attendee_count', $this->sortDirection);
                } else {
                    $eventsQuery->orderBy('start_date', $this->sortDirection);
                }

                $eventsQuery->with(['group', 'creator']);
                $events = $eventsQuery->paginate($this->perPage, ['*'], 'eventsPage');
                $results['events'] = $events;
                $total += $events instanceof LengthAwarePaginator ? $events->total() : $events->count();
            }

            $results['total'] = $total;
            return $results;
        });

        if (!$this->historyRecorded) {
            $this->recordSearchHistory($results['total'] ?? 0, $activeType, $locationFilter, $segments);
        }

        if (auth()->check()) {
            $this->suggestedContent = $this->buildSuggestedContent(auth()->user());
        }

        return $results;
    }

    /**
     * Refresh sidebar datasets such as history, saved searches, suggestions, and trending items.
     */
    protected function refreshSidebarData(bool $refreshTrending = true): void
    {
        if (!auth()->check()) {
            $this->searchHistory = collect();
            $this->savedSearches = collect();
            $this->suggestedContent = [
                'tags' => collect(),
                'users' => collect(),
                'pets' => collect(),
            ];

            if ($refreshTrending) {
                $this->trendingContent = [
                    'posts' => collect(),
                    'tags' => collect(),
                    'events' => collect(),
                ];
            }

            return;
        }

        $user = auth()->user();

        $this->searchHistory = $user->searchHistories()->limit(10)->get();
        $this->savedSearches = $user->savedSearches()->limit(10)->get();

        if ($refreshTrending) {
            $this->trendingContent = $this->buildTrendingContent();
        }

        $this->suggestedContent = $this->buildSuggestedContent($user);
    }

    /**
     * Build a standardised cache key for the unified search results set.
     */
    protected function buildCacheKey(string $type, string $filter, string $sortField, string $sortDirection, ?string $location, string $query): string
    {
        return "search_{$type}_{$filter}_{$sortField}_{$sortDirection}_" . md5($query . '|' . ($location ?? ''));
    }

    /**
     * Convert parsed segments into the set of tokens applied to text searches.
     */
    protected function prepareSearchTokens(array $segments): array
    {
        return collect(array_merge($segments['phrases'], $segments['terms']))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Apply search token conditions to the posts query including tag lookups.
     */
    protected function applySearchTokenConstraints($query, array $searchTokens, array $tagNames): void
    {
        $query->where(function ($nested) use ($searchTokens, $tagNames) {
            $applied = false;

            foreach ($searchTokens as $token) {
                if (!$applied) {
                    $nested->where('content', 'like', '%' . $token . '%');
                    $applied = true;
                } else {
                    $nested->orWhere('content', 'like', '%' . $token . '%');
                }
            }

            foreach ($tagNames as $tagName) {
                if (!$applied) {
                    $nested->whereHas('tags', function ($tagQuery) use ($tagName) {
                        $tagQuery->where('name', 'like', '%' . $tagName . '%');
                    });
                    $applied = true;
                } else {
                    $nested->orWhereHas('tags', function ($tagQuery) use ($tagName) {
                        $tagQuery->where('name', 'like', '%' . $tagName . '%');
                    });
                }
            }

            if (!$applied) {
                $nested->where('content', 'like', '%' . $this->query . '%');
            }
        });
    }

    /**
     * Apply tokenised conditions to the user query across primary columns.
     */
    protected function applyUserSearchTokenConstraints($query, array $searchTokens, array $tagNames): void
    {
        $query->where(function ($nested) use ($searchTokens) {
            $applied = false;
            $columns = ['name', 'email', 'bio', 'location'];
            $tokens = count($searchTokens) > 0 ? $searchTokens : [$this->query];

            foreach ($tokens as $token) {
                foreach ($columns as $column) {
                    if (!$applied) {
                        $nested->where($column, 'like', '%' . $token . '%');
                        $applied = true;
                    } else {
                        $nested->orWhere($column, 'like', '%' . $token . '%');
                    }
                }
            }
        });
    }

    /**
     * Resolve the user-specified type taking advanced operators into account.
     */
    protected function determineActiveType(array $segments): string
    {
        $requestedType = $segments['operators']['type'] ?? $this->type;
        $validTypes = ['all', 'posts', 'users', 'pets', 'tags', 'events'];
        $normalized = in_array($requestedType, $validTypes, true) ? $requestedType : 'all';

        if ($normalized !== $this->type) {
            $this->type = $normalized;
        }

        return $normalized;
    }

    /**
     * Persist the search history entry with all relevant filters.
     */
    protected function recordSearchHistory(int $totalResults, string $activeType, ?string $locationFilter, array $segments): void
    {
        if (!auth()->check() || empty($this->query)) {
            return;
        }

        $user = auth()->user();

        $filters = [
            'filter' => $this->filter,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'location' => $locationFilter,
            'tags' => $segments['tags'],
        ];

        SearchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'query' => Str::limit($this->query, 255, ''),
                'search_type' => $activeType,
            ],
            [
                'filters' => $filters,
                'results_count' => $totalResults,
            ]
        );

        $this->historyRecorded = true;
        $this->searchHistory = $user->searchHistories()->limit(10)->get();
    }

    /**
     * Parse the raw query string into normalised segments.
     */
    protected function resolveQuerySegments(): array
    {
        return $this->parseQuery($this->query);
    }

    /**
     * Parse a query string and extract terms, phrases, tags, and operators.
     */
    protected function parseQuery(string $rawQuery): array
    {
        $segments = [
            'terms' => [],
            'phrases' => [],
            'tags' => [],
            'operators' => [
                'type' => null,
                'location' => null,
            ],
        ];

        $rawQuery = trim($rawQuery);

        if ($rawQuery === '') {
            return $segments;
        }

        preg_match_all('~"([^"]+)"~u', $rawQuery, $phraseMatches);

        foreach ($phraseMatches[1] as $phrase) {
            $segments['phrases'][] = trim($phrase);
        }

        $rawQuery = preg_replace('~"([^"]+)"~u', ' ', $rawQuery);

        preg_match_all('~#([\pL\pN_\-]+)~u', $rawQuery, $tagMatches);

        foreach ($tagMatches[1] as $tag) {
            $segments['tags'][] = Str::lower($tag);
        }

        $rawQuery = preg_replace('~#([\pL\pN_\-]+)~u', ' ', $rawQuery);

        preg_match_all('~(type|location|near|tag):(\"[^\"]+\"|\'[^\']+\'|\S+)~u', $rawQuery, $operatorMatches, PREG_SET_ORDER);

        foreach ($operatorMatches as $match) {
            $operator = Str::lower($match[1]);
            $value = trim($match[2], "\"' ");

            if ($operator === 'type') {
                $segments['operators']['type'] = Str::lower($value);
            } elseif (in_array($operator, ['location', 'near'], true)) {
                $segments['operators']['location'] = $value;
            } elseif ($operator === 'tag') {
                $segments['tags'][] = Str::lower($value);
            }
        }

        $rawQuery = preg_replace('~(type|location|near|tag):(\"[^\"]+\"|\'[^\']+\'|\S+)~u', ' ', $rawQuery);

        $terms = preg_split('/\s+/', trim($rawQuery));

        foreach ($terms as $term) {
            if ($term !== '') {
                $segments['terms'][] = $term;
            }
        }

        $segments['tags'] = collect($segments['tags'])->filter()->unique()->values()->all();

        return $segments;
    }

    /**
     * Aggregate trending data for the discovery sidebar.
     */
    protected function buildTrendingContent(): array
    {
        $since = now()->subDays(7);

        $trendingPosts = Post::query()
            ->with(['user', 'pet'])
            ->withCount(['reactions as recent_reactions_count' => function ($query) use ($since) {
                $query->where('reactions.created_at', '>=', $since);
            }])
            ->where('created_at', '>=', $since)
            ->orderByDesc('recent_reactions_count')
            ->limit(5)
            ->get();

        $trendingTags = Tag::query()
            ->withCount(['posts as recent_posts_count' => function ($query) use ($since) {
                $query->where('posts.created_at', '>=', $since);
            }])
            ->orderByDesc('recent_posts_count')
            ->limit(5)
            ->get();

        $trendingEvents = Event::query()
            ->where('is_published', true)
            ->where('start_date', '>=', now()->subDay())
            ->withCount(['going as attendee_count'])
            ->orderByDesc('attendee_count')
            ->limit(5)
            ->get();

        return [
            'posts' => $trendingPosts,
            'tags' => $trendingTags,
            'events' => $trendingEvents,
        ];
    }

    /**
     * Build AI-style content suggestions using user history and profile metadata.
     */
    protected function buildSuggestedContent(User $user): array
    {
        $historyQueries = $user->searchHistories()->limit(15)->pluck('query');
        $historyTags = collect();

        foreach ($historyQueries as $historyQuery) {
            $parsed = $this->parseQuery($historyQuery);
            $historyTags = $historyTags->merge($parsed['tags']);
        }

        $tagNames = $historyTags->unique()->take(10);

        if ($tagNames->isEmpty()) {
            $tagNames = Tag::query()->orderByDesc('created_at')->limit(10)->pluck('name');
        }

        $suggestedTags = Tag::query()
            ->whereIn('name', $tagNames)
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get();

        $location = $user->location;
        $blockedIds = $user->blocks ? $user->blocks->pluck('id')->toArray() : [];

        $suggestedUsers = User::query()
            ->whereNotIn('id', array_merge([$user->id], $blockedIds))
            ->when($location, function ($query) use ($location) {
                $query->where('location', 'like', '%' . $location . '%');
            })
            ->limit(5)
            ->get();

        $suggestedPets = Pet::query()
            ->whereNotIn('user_id', $blockedIds)
            ->when($location, function ($query) use ($location) {
                $query->where('location', 'like', '%' . $location . '%');
            })
            ->limit(5)
            ->get();

        return [
            'tags' => $suggestedTags,
            'users' => $suggestedUsers,
            'pets' => $suggestedPets,
        ];
    }

    /**
     * Allow users to save the currently configured search.
     */
    public function saveCurrentSearch(): void
    {
        if (!auth()->check() || empty($this->query)) {
            return;
        }

        $validator = Validator::make(
            ['name' => $this->newSavedSearchName],
            ['name' => ['required', 'string', 'max:120']]
        );

        if ($validator->fails()) {
            $this->resetErrorBag();

            foreach ($validator->errors()->get('name') as $message) {
                $this->addError('newSavedSearchName', $message);
            }

            return;
        }

        $filters = [
            'filter' => $this->filter,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'location' => $this->location,
        ];

        SavedSearch::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => $this->newSavedSearchName,
            ],
            [
                'query' => $this->query,
                'search_type' => $this->type,
                'filters' => $filters,
            ]
        );

        $this->newSavedSearchName = '';
        $this->refreshSidebarData(false);
    }

    /**
     * Apply a saved search and re-run the discovery query.
     */
    public function applySavedSearch(int $savedSearchId): void
    {
        if (!auth()->check()) {
            return;
        }

        $savedSearch = auth()->user()->savedSearches()->find($savedSearchId);

        if (!$savedSearch) {
            return;
        }

        $filters = $savedSearch->filters ?? [];

        $this->query = $savedSearch->query;
        $this->type = $savedSearch->search_type;
        $this->filter = $filters['filter'] ?? 'all';
        $this->sortField = $filters['sort_field'] ?? 'created_at';
        $this->sortDirection = $filters['sort_direction'] ?? 'desc';
        $this->location = $filters['location'] ?? '';

        $savedSearch->increment('run_count');
        $this->clearSearchCache();
    }

    /**
     * Remove a saved search record from the current account.
     */
    public function deleteSavedSearch(int $savedSearchId): void
    {
        if (!auth()->check()) {
            return;
        }

        auth()->user()->savedSearches()->where('id', $savedSearchId)->delete();
        $this->refreshSidebarData(false);
    }

    /**
     * Re-run a previous search from history.
     */
    public function rerunSearchFromHistory(int $historyId): void
    {
        if (!auth()->check()) {
            return;
        }

        $history = auth()->user()->searchHistories()->find($historyId);

        if (!$history) {
            return;
        }

        $filters = $history->filters ?? [];

        $this->query = $history->query;
        $this->type = $history->search_type;
        $this->filter = $filters['filter'] ?? 'all';
        $this->sortField = $filters['sort_field'] ?? 'created_at';
        $this->sortDirection = $filters['sort_direction'] ?? 'desc';
        $this->location = $filters['location'] ?? '';

        $this->clearSearchCache();
    }
    
    public function render()
    {
        return view('livewire.common.unified-search', [
            'results' => $this->getSearchResults(),
        ])->layout('layouts.app');
    }
}
