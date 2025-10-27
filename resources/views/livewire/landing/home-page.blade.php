<div class="flex-1">
    {{-- Top navigation reflecting the clean minimal header from the reference design. --}}
    <header class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <x-application-logo class="h-10 w-auto text-indigo-600" />
                <span class="text-xl font-semibold text-slate-900">{{ __('Pet Social Network') }}</span>
            </a>
            <nav class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-slate-600 transition hover:bg-slate-100">{{ __('Sign In') }}</a>
                <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-4 py-2 text-white shadow-sm transition hover:bg-slate-800">{{ __('Sign Up') }}</a>
            </nav>
        </div>
    </header>

    {{-- Hero and metrics section inspired by the V0 concept. --}}
    <section class="bg-gradient-to-b from-white via-slate-50 to-white">
        <div class="mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-900/5 px-4 py-2 text-sm font-medium text-slate-700">
                        <x-icons.paw class="h-4 w-4 text-slate-900" />
                        <span>{{ __('The social network for pet lovers') }}</span>
                    </div>
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                        {{ __('Connect, share, and learn about your pets') }}
                    </h1>
                    <p class="text-lg leading-relaxed text-slate-600">
                        {{ __('Join a vibrant community of caretakers, shelters, and pet enthusiasts. Share adventures, discover new care tips, and celebrate every milestone together.') }}
                    </p>
                    <div class="flex flex-wrap gap-6">
                        @foreach ($stats as $stat)
                            <div class="flex min-w-[180px] items-center gap-3 rounded-xl border border-slate-200 bg-white/80 px-5 py-4 shadow-sm">
                                <x-dynamic-component :component="'icons.' . $stat['icon']" class="h-6 w-6 text-slate-900" />
                                <div>
                                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($stat['value']) }}+</p>
                                    <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Authentication card mirroring the split-pane composition. --}}
                <div class="flex justify-center">
                    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
                        <h2 class="mb-6 text-center text-2xl font-semibold text-slate-900">{{ __('Join the community') }}</h2>
                        <p class="mb-8 text-center text-sm text-slate-600">
                            {{ __('Sign in if you already have an account or register to start building your pet profile today.') }}
                        </p>
                        <div class="space-y-4">
                            <a href="{{ route('login') }}" class="block w-full rounded-full bg-slate-900 px-5 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">{{ __('Sign In') }}</a>
                            <a href="{{ route('register') }}" class="block w-full rounded-full border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">{{ __('Create Account') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature highlights demonstrating the platform pillars. --}}
    <section class="py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ __('Everything you need for your pet community') }}</h2>
                <p class="mt-4 text-lg text-slate-600">{{ __('Explore tools crafted for pet storytelling, care, and connection.') }}</p>
            </div>
            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white/70 p-8 shadow-sm">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900/10 text-slate-900">
                        <x-icons.paw class="h-6 w-6" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900">{{ __('Pet profiles') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        {{ __('Build rich profiles for each companion with galleries, milestones, and favorite memories.') }}
                    </p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white/70 p-8 shadow-sm">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900/10 text-slate-900">
                        <x-icons.book class="h-6 w-6" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900">{{ __('Care library') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        {{ __('Browse training tips, nutrition guides, and health checklists tailored to every species.') }}
                    </p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white/70 p-8 shadow-sm">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900/10 text-slate-900">
                        <x-icons.heart class="h-6 w-6" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900">{{ __('Social moments') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        {{ __('Celebrate adoptions, playdates, and everyday wins with a supportive global network.') }}
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- Trending stories inspired by the design’s “Trending Stories” grid. --}}
    @if (! empty($featuredPosts))
        <section class="bg-slate-50 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-3xl font-bold text-slate-900">
                            <x-icons.activity class="h-7 w-7 text-slate-900" />
                            {{ __('Trending stories') }}
                        </h2>
                        <p class="text-sm text-slate-600">{{ __('Discover the moments everyone is talking about right now.') }}</p>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-white">
                        {{ __('Explore the feed') }}
                        <x-icons.arrow-right class="h-4 w-4" />
                    </a>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($featuredPosts as $post)
                        <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                            @if ($post['image'])
                                <img src="{{ $post['image'] }}" alt="{{ $post['pet_name'] }}" class="aspect-video w-full object-cover" />
                            @else
                                <div class="aspect-video w-full bg-gradient-to-br from-slate-200 via-slate-100 to-white">
                                    <div class="flex h-full items-center justify-center text-4xl font-semibold text-slate-400">
                                        {{ mb_substr($post['pet_name'], 0, 1) }}
                                    </div>
                                </div>
                            @endif
                            <div class="flex flex-1 flex-col gap-3 px-6 py-5">
                                <h3 class="line-clamp-2 text-lg font-semibold text-slate-900">{{ $post['title'] }}</h3>
                                <p class="line-clamp-3 text-sm text-slate-600">{{ $post['excerpt'] }}</p>
                                <div class="mt-auto flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ __('Shared by') }} {{ $post['pet_name'] }}</span>
                                    <span class="inline-flex items-center gap-1 text-slate-600">
                                        <x-icons.heart class="h-4 w-4 text-rose-500" />
                                        {{ number_format($post['likes']) }}
                                    </span>
                                </div>
                                @if (! empty($post['tags']))
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        @foreach ($post['tags'] as $tag)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Closing call-to-action mirrored from the provided design. --}}
    <section class="py-20">
        <div class="mx-auto max-w-5xl rounded-3xl bg-slate-900 px-10 py-14 text-center text-white shadow-2xl">
            <h2 class="text-3xl font-bold">{{ $ctaHeadline }}</h2>
            <p class="mt-4 text-base text-slate-200">{{ $ctaMessage }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-100">{{ __('Create your free account') }}</a>
                <a href="{{ route('login') }}" class="rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">{{ __('Sign in instead') }}</a>
            </div>
        </div>
    </section>

    {{-- Footer keeping the layout polished and informative. --}}
    <footer class="border-t border-slate-200 bg-white/70">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <x-application-logo class="h-8 w-auto text-slate-900" />
                <span class="font-semibold text-slate-700">{{ __('Pet Social Network') }}</span>
            </div>
            <p class="text-xs">© {{ now()->year }} {{ __('Pet Social Network') }} • {{ __('All rights reserved.') }}</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="transition hover:text-slate-700">{{ __('Privacy') }}</a>
                <a href="{{ route('login') }}" class="transition hover:text-slate-700">{{ __('Terms') }}</a>
                <a href="{{ route('login') }}" class="transition hover:text-slate-700">{{ __('Support') }}</a>
            </div>
        </div>
    </footer>
</div>
