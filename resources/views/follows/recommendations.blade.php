<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('follows.people_you_may_follow') }}
            </h2>
            <a href="{{ route('follows.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.back class="h-5 w-5 mr-2" stroke-width="2" />
                {{ __('follows.back_to_followers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('follows.based_on_location') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('follows.people_near_you') }}</p>
                    </div>
                    
                    @if($recommendations->isEmpty())
                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                            <x-icons.users class="h-12 w-12 mx-auto text-gray-400" stroke-width="2" />
                            <p class="mt-2 text-gray-500">{{ __('follows.no_recommendations') }}</p>
                            <p class="text-sm text-gray-400">{{ __('follows.update_profile_suggestion') }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($recommendations as $user)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img class="h-12 w-12 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between">
                                                <a href="{{ route('profile', $user) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $user->name }}</a>
                                            </div>
                                            
                                            @if($user->location)
                                                <p class="text-xs text-gray-500 mt-1">{{ $user->location }}</p>
                                            @endif
                                            
                                            <div class="mt-3 flex space-x-2">
                                                @livewire('common.follow.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $user->id], key('follow-'.$user->id))
                                                
                                                @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $user->id], key('friend-'.$user->id))
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
