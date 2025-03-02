<div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 flex items-center">
                <x-icons.download class="h-5 w-5 mr-2 text-indigo-500" stroke-width="2" />
                Export {{ $entityType === 'pet' ? 'Pet ' : '' }}Friends
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="exportType" class="block text-sm font-medium text-gray-700 mb-1">Export Type</label>
                    <select 
                        wire:model="exportType" 
                        id="exportType" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="friends">Friends</option>
                        @if($entityType === 'user')
                            <option value="followers">Followers</option>
                            <option value="following">Following</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label for="exportFormat" class="block text-sm font-medium text-gray-700 mb-1">Export Format</label>
                    <select 
                        wire:model="exportFormat" 
                        id="exportFormat" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                        <option value="vcf">vCard (VCF)</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="inline-flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="includeEmails" 
                            id="includeEmails" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Include Email Addresses</span>
                    </label>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="includePhones" 
                            id="includePhones" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Include Phone Numbers</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.search class="h-5 w-5 text-gray-400" stroke-width="2" />
                </div>
                <input 
                    type="text" 
                    wire:model.debounce.300ms="search" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                    placeholder="Search {{ $exportType }}..."
                >
            </div>
            
            <div class="overflow-x-auto rounded-md border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectAll" 
                                        id="selectAll" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            @if($includeEmails)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            @endif
                            @if($includePhones)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="inline-flex items-center">
                                        <input 
                                            type="checkbox" 
                                            wire:model="selectedFriends" 
                                            value="{{ $user->id }}" 
                                            id="user-{{ $user->id }}" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        >
                                        <span class="sr-only">Select {{ $user->name }}</span>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img class="h-8 w-8 rounded-full object-cover" src="{{ $user->avatar ?? '/images/default-avatar.png' }}" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">@{{ $user->username }}</td>
                                @if($includeEmails)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                @endif
                                @if($includePhones)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->phone }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + ($includeEmails ? 1 : 0) + ($includePhones ? 1 : 0) }}" class="px-6 py-10 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <x-icons.exclamation-circle class="h-10 w-10 text-gray-400 mb-2" stroke-width="1" />
                                        <p>No {{ $exportType }} found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <button 
                wire:click="export" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ empty($selectedFriends) ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ empty($selectedFriends) ? 'disabled' : '' }}
            >
                <x-icons.download class="h-4 w-4 mr-2" stroke-width="2" />
                Export Selected
            </button>
            <span class="text-sm text-gray-500">
                {{ count($selectedFriends) }} {{ Str::plural('item', count($selectedFriends)) }} selected
            </span>
        </div>
    </div>
</div>
