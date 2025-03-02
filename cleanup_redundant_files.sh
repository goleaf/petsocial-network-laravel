#!/bin/bash

# Script to safely remove redundant files after component optimization
# Created on: 2025-03-02

# Define backup directory
BACKUP_DIR="/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/backup_components_$(date +%Y%m%d_%H%M%S)"
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

echo "Starting cleanup of redundant components..."

# Social/Friend components replaced by Common components
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Social/Friend/Activity.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Social/Friend/Analytics.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Social/Friend/Finder.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Social/Friend/List.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Social/Friend/Requests.php"

# Pet components replaced by Common components
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Pet/FriendFinder.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Pet/FriendsList.php"
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Livewire/Pet/PetFriends.php"

# Redundant controllers
backup_file "/Users/andrejprus/Library/CloudStorage/Dropbox/projects/pets-social-network/src/app/Http/Controllers/Social/FriendshipController.php"

echo "Cleanup complete. All files have been backed up to: $BACKUP_DIR"
echo "You can restore any files if needed from the backup directory."
