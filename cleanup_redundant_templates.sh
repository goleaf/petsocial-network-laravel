#!/bin/bash

# Script to safely remove redundant blade templates after component optimization
# Created on: 2025-03-02

# Define backup directory
BACKUP_DIR="/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/backup_templates_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
echo "Created backup directory: $BACKUP_DIR"

# Function to safely move files to backup
backup_file() {
    if [ -f "$1" ]; then
        # Create directory structure in backup
        RELATIVE_PATH=${1#/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/}
        BACKUP_PATH="$BACKUP_DIR/$(dirname "$RELATIVE_PATH")"
        mkdir -p "$BACKUP_PATH"
        
        # Copy file to backup
        cp "$1" "$BACKUP_PATH/"
        echo "Backed up: $1"
        
        # Remove the original file
        rm "$1"
        echo "Removed: $1"
    else
        echo "File not found: $1"
    fi
}

echo "Starting cleanup of redundant blade templates..."

# Friend templates replaced by Common templates
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friend/activity-log.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friend/button.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friend/finder.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friend/hub.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friend/list.blade.php"

# Friends templates replaced by Common templates
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/activity.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/analytics.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/button.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/dashboard.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/finder.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/friends/requests.blade.php"

# Pet templates replaced by Common templates
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/activity-log.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friend-analytics.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friend-button.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friend-finder.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friend-hub.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friends-list.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet/friends.blade.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/resources/views/livewire/pet-friends.blade.php"

echo "Cleanup complete. All files have been backed up to: $BACKUP_DIR"
echo "You can restore any files if needed from the backup directory."
