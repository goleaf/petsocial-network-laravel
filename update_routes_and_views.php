<?php

// Script to update routes and views to use the new merged components
$routesFile = __DIR__ . '/routes/web.php';
$viewsPath = __DIR__ . '/resources/views/livewire';
$commonViewsPath = $viewsPath . '/common';

// Create the common views directory if it doesn't exist
if (!is_dir($commonViewsPath)) {
    mkdir($commonViewsPath, 0755, true);
}

// Define component mappings
$componentMappings = [
    'App\\Http\\Livewire\\Pet\\FriendButton' => 'App\\Http\\Livewire\\Common\\FriendButton',
    'App\\Http\\Livewire\\Social\\Friend\\Button' => 'App\\Http\\Livewire\\Common\\FriendButton',
    'App\\Http\\Livewire\\Pet\\FriendsList' => 'App\\Http\\Livewire\\Common\\FriendsList',
    'App\\Http\\Livewire\\Social\\Friend\\List' => 'App\\Http\\Livewire\\Common\\FriendsList',
    'App\\Http\\Livewire\\Pet\\FriendHub' => 'App\\Http\\Livewire\\Common\\FriendHub',
    'App\\Http\\Livewire\\Social\\Friend\\Dashboard' => 'App\\Http\\Livewire\\Common\\FriendHub',
    'App\\Http\\Livewire\\Pet\\FriendFinder' => 'App\\Http\\Livewire\\Common\\FriendFinder',
    'App\\Http\\Livewire\\Social\\Friend\\Finder' => 'App\\Http\\Livewire\\Common\\FriendFinder',
    'App\\Http\\Livewire\\Pet\\ActivityLog' => 'App\\Http\\Livewire\\Common\\ActivityLog',
    'App\\Http\\Livewire\\Social\\Friend\\Activity' => 'App\\Http\\Livewire\\Common\\ActivityLog'
];

// Define view mappings
$viewMappings = [
    'livewire.pet.friend-button' => 'livewire.common.friend-button',
    'livewire.social.friend.button' => 'livewire.common.friend-button',
    'livewire.pet.friends-list' => 'livewire.common.friends-list',
    'livewire.social.friend.list' => 'livewire.common.friends-list',
    'livewire.pet.friend-hub' => 'livewire.common.friend-hub',
    'livewire.social.friend.dashboard' => 'livewire.common.friend-hub',
    'livewire.pet.friend-finder' => 'livewire.common.friend-finder',
    'livewire.social.friend.finder' => 'livewire.common.friend-finder',
    'livewire.pet.activity-log' => 'livewire.common.activity-log',
    'livewire.social.friend.activity' => 'livewire.common.activity-log'
];

// Create common view files
$viewTemplates = [
    'friend-button' => '<div>
    <button wire:click="toggleFriendship" class="btn {{ $buttonClass }}">
        {{ $buttonText }}
    </button>
</div>',
    'friends-list' => '<div>
    <h3>{{ $title }}</h3>
    <div class="friends-list">
        @foreach($friends as $friend)
            <div class="friend-item">
                <div class="friend-avatar">
                    <img src="{{ $friend->avatar ?? \'/images/default-avatar.png\' }}" alt="{{ $friend->name }}">
                </div>
                <div class="friend-info">
                    <h4>{{ $friend->name }}</h4>
                    <p>{{ $friend->bio ?? \'\' }}</p>
                </div>
                <div class="friend-actions">
                    @livewire(\'common.friend-button\', [\'entityType\' => $entityType, \'entityId\' => $friend->id], key(\'friend-button-\'.$friend->id))
                </div>
            </div>
        @endforeach
    </div>
</div>',
    'friend-hub' => '<div>
    <h2>Friend Hub</h2>
    <div class="friend-hub-container">
        <div class="friend-requests">
            <h3>Friend Requests</h3>
            @foreach($friendRequests as $request)
                <div class="friend-request-item">
                    <div class="friend-avatar">
                        <img src="{{ $request->sender->avatar ?? \'/images/default-avatar.png\' }}" alt="{{ $request->sender->name }}">
                    </div>
                    <div class="friend-info">
                        <h4>{{ $request->sender->name }}</h4>
                        <p>{{ $request->sender->bio ?? \'\' }}</p>
                    </div>
                    <div class="friend-actions">
                        <button wire:click="acceptRequest({{ $request->id }})" class="btn btn-success">Accept</button>
                        <button wire:click="declineRequest({{ $request->id }})" class="btn btn-danger">Decline</button>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="friends-list">
            @livewire(\'common.friends-list\', [\'entityType\' => $entityType, \'entityId\' => $entityId], key(\'friends-list-\'.$entityId))
        </div>
    </div>
</div>',
    'friend-finder' => '<div>
    <h2>Find Friends</h2>
    <div class="search-container">
        <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search for friends...">
        <button wire:click="search" class="btn btn-primary">Search</button>
    </div>
    
    <div class="search-results">
        @foreach($searchResults as $result)
            <div class="result-item">
                <div class="result-avatar">
                    <img src="{{ $result->avatar ?? \'/images/default-avatar.png\' }}" alt="{{ $result->name }}">
                </div>
                <div class="result-info">
                    <h4>{{ $result->name }}</h4>
                    <p>{{ $result->bio ?? \'\' }}</p>
                </div>
                <div class="result-actions">
                    @livewire(\'common.friend-button\', [\'entityType\' => $entityType, \'entityId\' => $result->id], key(\'result-button-\'.$result->id))
                </div>
            </div>
        @endforeach
    </div>
</div>',
    'activity-log' => '<div>
    <h2>Activity Log</h2>
    <div class="activity-log-container">
        @foreach($activities as $activity)
            <div class="activity-item">
                <div class="activity-icon">
                    <!-- Icon based on activity type -->
                </div>
                <div class="activity-content">
                    <div class="activity-header">
                        <span class="activity-type">{{ $activity->type }}</span>
                        <span class="activity-time">{{ $activity->happened_at->diffForHumans() }}</span>
                    </div>
                    <div class="activity-description">
                        {{ $activity->description }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>'
];

// Create the view files
foreach ($viewTemplates as $view => $content) {
    $viewPath = $commonViewsPath . '/' . $view . '.blade.php';
    file_put_contents($viewPath, $content);
    echo "Created view: $viewPath\n";
}

// Update routes file
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    // Replace component references in routes
    foreach ($componentMappings as $oldComponent => $newComponent) {
        $routesContent = str_replace($oldComponent, $newComponent, $routesContent);
    }
    
    file_put_contents($routesFile, $routesContent);
    echo "Updated routes file: $routesFile\n";
}

// Find and update blade files that reference the old components
$bladeFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $bladeFiles[] = $file->getPathname();
    }
}

foreach ($bladeFiles as $bladeFile) {
    $bladeContent = file_get_contents($bladeFile);
    $updated = false;
    
    // Replace view references
    foreach ($viewMappings as $oldView => $newView) {
        if (strpos($bladeContent, $oldView) !== false) {
            $bladeContent = str_replace($oldView, $newView, $bladeContent);
            $updated = true;
        }
    }
    
    // Replace livewire component references
    foreach ($componentMappings as $oldComponent => $newComponent) {
        $oldComponentShort = substr($oldComponent, strrpos($oldComponent, '\\') + 1);
        $newComponentShort = substr($newComponent, strrpos($newComponent, '\\') + 1);
        
        // Replace @livewire('pet.friend-button') with @livewire('common.friend-button')
        $oldPattern = "@livewire('" . strtolower(str_replace('\\', '.', substr($oldComponent, 15))) . "'";
        $newPattern = "@livewire('" . strtolower(str_replace('\\', '.', substr($newComponent, 15))) . "'";
        
        if (strpos($bladeContent, $oldPattern) !== false) {
            $bladeContent = str_replace($oldPattern, $newPattern, $bladeContent);
            $updated = true;
        }
    }
    
    if ($updated) {
        file_put_contents($bladeFile, $bladeContent);
        echo "Updated blade file: $bladeFile\n";
    }
}

echo "\nRoutes and views update completed.\n";
