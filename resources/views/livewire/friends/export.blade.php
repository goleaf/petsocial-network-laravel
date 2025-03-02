<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Export Contacts</h1>
    
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
        
        @if (session()->has('download'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">
                    Your file is ready for download: 
                    <a href="{{ session('download.url') }}" class="underline font-bold" download="{{ session('download.filename') }}">
                        {{ session('download.filename') }}
                    </a>
                </span>
            </div>
        @endif
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Export Settings
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Choose which contacts to export and in what format
            </p>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label for="exportType" class="block text-sm font-medium text-gray-700">Export Type</label>
                        <select id="exportType" wire:model="exportType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="friends">Friends</option>
                            <option value="followers">Followers</option>
                            <option value="following">Following</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="exportFormat" class="block text-sm font-medium text-gray-700">Export Format</label>
                        <select id="exportFormat" wire:model="exportFormat" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="csv">CSV (Spreadsheet)</option>
                            <option value="json">JSON</option>
                            <option value="vcf">VCF (Contact Cards)</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="includeEmails" wire:model="includeEmails" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="includeEmails" class="font-medium text-gray-700">Include Email Addresses</label>
                                <p class="text-gray-500">Export will include email addresses if available</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="includePhones" wire:model="includePhones" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="includePhones" class="font-medium text-gray-700">Include Phone Numbers</label>
                                <p class="text-gray-500">Export will include phone numbers if available</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button wire:click="export" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Selected Contacts
                        </button>
                    </div>
                </div>
                
                <div>
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Contacts</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" wire:model.debounce.300ms="search" id="search" class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-10 sm:text-sm border-gray-300 rounded-md" placeholder="Search by name...">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center">
                                <input id="selectAll" wire:model="selectAll" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label for="selectAll" class="ml-2 block text-sm text-gray-900">
                                    Select All
                                </label>
                            </div>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto">
                            <ul class="divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <li class="px-4 py-3">
                                        <div class="flex items-center">
                                            <input id="user-{{ $user->id }}" wire:model="selectedFriends" value="{{ $user->id }}" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="user-{{ $user->id }}" class="ml-3 block">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ '@' . $user->username }}</div>
                                            </label>
                                        </div>
                                    </li>
                                @empty
                                    <li class="px-4 py-6 text-center text-gray-500">
                                        No contacts found in this category.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-2 text-sm text-gray-500">
                        {{ count($selectedFriends) }} contacts selected
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
