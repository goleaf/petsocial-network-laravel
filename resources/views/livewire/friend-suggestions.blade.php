<div class="bg-white p-6 rounded-lg shadow mb-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Friend Suggestions</h2>
    @if ($suggestions->isEmpty())
        <p class="text-gray-500">No suggestions right now.</p>
    @else
        <ul class="space-y-2">
            @foreach ($suggestions as $user)
                <li class="flex items-center justify-between mb-2">
                    <a href="{{ route('profile', $user) }}" class="text-blue-500 hover:underline">{{ $user->name }}</a>
                    @livewire('friend-button', ['userId' => $user->id], key('friend-suggestion-'.$user->id))
                </li>
            @endforeach
        </ul>
    @endif
</div>
