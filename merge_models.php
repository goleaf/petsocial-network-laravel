<?php

// Script to merge duplicate model functionality
$modelsPath = __DIR__ . '/app/Models';

// Define models to update
$modelsToUpdate = [
    [
        'file' => $modelsPath . '/User.php',
        'traits' => ['HasFriendships', 'HasPolymorphicRelations']
    ],
    [
        'file' => $modelsPath . '/Pet.php',
        'traits' => ['HasFriendships', 'HasPolymorphicRelations']
    ]
];

// Create the traits
$traitsPath = $modelsPath . '/Traits';
if (!is_dir($traitsPath)) {
    mkdir($traitsPath, 0755, true);
}

// Create HasFriendships trait
$hasFriendshipsContent = "<?php\n\n";
$hasFriendshipsContent .= "namespace App\\Models\\Traits;\n\n";
$hasFriendshipsContent .= "use App\\Models\\Friendship;\n";
$hasFriendshipsContent .= "use App\\Models\\PetFriendship;\n";
$hasFriendshipsContent .= "use App\\Models\\User;\n";
$hasFriendshipsContent .= "use App\\Models\\Pet;\n\n";
$hasFriendshipsContent .= "trait HasFriendships\n";
$hasFriendshipsContent .= "{\n";
$hasFriendshipsContent .= "    /**\n";
$hasFriendshipsContent .= "     * Get all friendships for this model\n";
$hasFriendshipsContent .= "     */\n";
$hasFriendshipsContent .= "    public function friendships()\n";
$hasFriendshipsContent .= "    {\n";
$hasFriendshipsContent .= "        if (\$this instanceof User) {\n";
$hasFriendshipsContent .= "            return \$this->hasMany(Friendship::class, 'sender_id')\n";
$hasFriendshipsContent .= "                ->orWhere('recipient_id', \$this->id);\n";
$hasFriendshipsContent .= "        } elseif (\$this instanceof Pet) {\n";
$hasFriendshipsContent .= "            return \$this->hasMany(PetFriendship::class, 'pet_id')\n";
$hasFriendshipsContent .= "                ->orWhere('friend_pet_id', \$this->id);\n";
$hasFriendshipsContent .= "        }\n";
$hasFriendshipsContent .= "    }\n\n";
$hasFriendshipsContent .= "    /**\n";
$hasFriendshipsContent .= "     * Get all friends for this model\n";
$hasFriendshipsContent .= "     */\n";
$hasFriendshipsContent .= "    public function friends()\n";
$hasFriendshipsContent .= "    {\n";
$hasFriendshipsContent .= "        if (\$this instanceof User) {\n";
$hasFriendshipsContent .= "            \$sentFriendships = Friendship::where('sender_id', \$this->id)\n";
$hasFriendshipsContent .= "                ->where('status', 'accepted')\n";
$hasFriendshipsContent .= "                ->pluck('recipient_id');\n\n";
$hasFriendshipsContent .= "            \$receivedFriendships = Friendship::where('recipient_id', \$this->id)\n";
$hasFriendshipsContent .= "                ->where('status', 'accepted')\n";
$hasFriendshipsContent .= "                ->pluck('sender_id');\n\n";
$hasFriendshipsContent .= "            \$friendIds = \$sentFriendships->merge(\$receivedFriendships);\n\n";
$hasFriendshipsContent .= "            return User::whereIn('id', \$friendIds)->get();\n";
$hasFriendshipsContent .= "        } elseif (\$this instanceof Pet) {\n";
$hasFriendshipsContent .= "            \$friendships = PetFriendship::where('pet_id', \$this->id)\n";
$hasFriendshipsContent .= "                ->where('status', 'accepted')\n";
$hasFriendshipsContent .= "                ->pluck('friend_pet_id');\n\n";
$hasFriendshipsContent .= "            \$reverseFriendships = PetFriendship::where('friend_pet_id', \$this->id)\n";
$hasFriendshipsContent .= "                ->where('status', 'accepted')\n";
$hasFriendshipsContent .= "                ->pluck('pet_id');\n\n";
$hasFriendshipsContent .= "            \$friendIds = \$friendships->merge(\$reverseFriendships);\n\n";
$hasFriendshipsContent .= "            return Pet::whereIn('id', \$friendIds)->get();\n";
$hasFriendshipsContent .= "        }\n";
$hasFriendshipsContent .= "    }\n\n";
$hasFriendshipsContent .= "    /**\n";
$hasFriendshipsContent .= "     * Check if this model is friends with another model\n";
$hasFriendshipsContent .= "     */\n";
$hasFriendshipsContent .= "    public function isFriendsWith(\$model)\n";
$hasFriendshipsContent .= "    {\n";
$hasFriendshipsContent .= "        if (\$this instanceof User && \$model instanceof User) {\n";
$hasFriendshipsContent .= "            return Friendship::where(function (\$query) use (\$model) {\n";
$hasFriendshipsContent .= "                \$query->where('sender_id', \$this->id)\n";
$hasFriendshipsContent .= "                    ->where('recipient_id', \$model->id);\n";
$hasFriendshipsContent .= "            })->orWhere(function (\$query) use (\$model) {\n";
$hasFriendshipsContent .= "                \$query->where('sender_id', \$model->id)\n";
$hasFriendshipsContent .= "                    ->where('recipient_id', \$this->id);\n";
$hasFriendshipsContent .= "            })->where('status', 'accepted')->exists();\n";
$hasFriendshipsContent .= "        } elseif (\$this instanceof Pet && \$model instanceof Pet) {\n";
$hasFriendshipsContent .= "            return PetFriendship::where(function (\$query) use (\$model) {\n";
$hasFriendshipsContent .= "                \$query->where('pet_id', \$this->id)\n";
$hasFriendshipsContent .= "                    ->where('friend_pet_id', \$model->id);\n";
$hasFriendshipsContent .= "            })->orWhere(function (\$query) use (\$model) {\n";
$hasFriendshipsContent .= "                \$query->where('pet_id', \$model->id)\n";
$hasFriendshipsContent .= "                    ->where('friend_pet_id', \$this->id);\n";
$hasFriendshipsContent .= "            })->where('status', 'accepted')->exists();\n";
$hasFriendshipsContent .= "        }\n";
$hasFriendshipsContent .= "        \n";
$hasFriendshipsContent .= "        return false;\n";
$hasFriendshipsContent .= "    }\n";
$hasFriendshipsContent .= "}\n";

file_put_contents($traitsPath . '/HasFriendships.php', $hasFriendshipsContent);
echo "Created HasFriendships trait\n";

// Create HasPolymorphicRelations trait
$hasPolymorphicRelationsContent = "<?php\n\n";
$hasPolymorphicRelationsContent .= "namespace App\\Models\\Traits;\n\n";
$hasPolymorphicRelationsContent .= "use App\\Models\\PetActivity;\n";
$hasPolymorphicRelationsContent .= "use App\\Models\\Attachment;\n";
$hasPolymorphicRelationsContent .= "use App\\Models\\Comment;\n";
$hasPolymorphicRelationsContent .= "use App\\Models\\Reaction;\n\n";
$hasPolymorphicRelationsContent .= "trait HasPolymorphicRelations\n";
$hasPolymorphicRelationsContent .= "{\n";
$hasPolymorphicRelationsContent .= "    /**\n";
$hasPolymorphicRelationsContent .= "     * Get all activities where this model is the actor\n";
$hasPolymorphicRelationsContent .= "     */\n";
$hasPolymorphicRelationsContent .= "    public function activities()\n";
$hasPolymorphicRelationsContent .= "    {\n";
$hasPolymorphicRelationsContent .= "        return \$this->morphMany(PetActivity::class, 'actor');\n";
$hasPolymorphicRelationsContent .= "    }\n\n";
$hasPolymorphicRelationsContent .= "    /**\n";
$hasPolymorphicRelationsContent .= "     * Get all activities where this model is the target\n";
$hasPolymorphicRelationsContent .= "     */\n";
$hasPolymorphicRelationsContent .= "    public function targetedActivities()\n";
$hasPolymorphicRelationsContent .= "    {\n";
$hasPolymorphicRelationsContent .= "        return \$this->morphMany(PetActivity::class, 'target');\n";
$hasPolymorphicRelationsContent .= "    }\n\n";
$hasPolymorphicRelationsContent .= "    /**\n";
$hasPolymorphicRelationsContent .= "     * Get all attachments for this model\n";
$hasPolymorphicRelationsContent .= "     */\n";
$hasPolymorphicRelationsContent .= "    public function attachments()\n";
$hasPolymorphicRelationsContent .= "    {\n";
$hasPolymorphicRelationsContent .= "        return \$this->morphMany(Attachment::class, 'attachable');\n";
$hasPolymorphicRelationsContent .= "    }\n\n";
$hasPolymorphicRelationsContent .= "    /**\n";
$hasPolymorphicRelationsContent .= "     * Get all comments for this model\n";
$hasPolymorphicRelationsContent .= "     */\n";
$hasPolymorphicRelationsContent .= "    public function comments()\n";
$hasPolymorphicRelationsContent .= "    {\n";
$hasPolymorphicRelationsContent .= "        return \$this->morphMany(Comment::class, 'commentable');\n";
$hasPolymorphicRelationsContent .= "    }\n\n";
$hasPolymorphicRelationsContent .= "    /**\n";
$hasPolymorphicRelationsContent .= "     * Get all reactions for this model\n";
$hasPolymorphicRelationsContent .= "     */\n";
$hasPolymorphicRelationsContent .= "    public function reactions()\n";
$hasPolymorphicRelationsContent .= "    {\n";
$hasPolymorphicRelationsContent .= "        return \$this->morphMany(Reaction::class, 'reactable');\n";
$hasPolymorphicRelationsContent .= "    }\n";
$hasPolymorphicRelationsContent .= "}\n";

file_put_contents($traitsPath . '/HasPolymorphicRelations.php', $hasPolymorphicRelationsContent);
echo "Created HasPolymorphicRelations trait\n";

// Update models to use the traits
foreach ($modelsToUpdate as $model) {
    $file = $model['file'];
    $traits = $model['traits'];
    
    if (!file_exists($file)) {
        echo "Model file doesn't exist: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if the model already uses the traits
    $useTraitsStatements = [];
    foreach ($traits as $trait) {
        if (strpos($content, "use App\\Models\\Traits\\$trait;") === false) {
            $useTraitsStatements[] = "use App\\Models\\Traits\\$trait;";
        }
    }
    
    if (!empty($useTraitsStatements)) {
        // Add use statements after the namespace
        $content = preg_replace(
            '/(namespace App\\\\Models;.*?)use /s',
            '$1' . implode("\n", $useTraitsStatements) . "\n\nuse ",
            $content
        );
        
        // Add use trait statement in the class
        $traitUseStatement = "    use " . implode(", ", $traits) . ";\n";
        $content = preg_replace(
            '/(class \w+ extends Model.*?{)/s',
            '$1' . "\n" . $traitUseStatement,
            $content
        );
        
        file_put_contents($file, $content);
        echo "Updated model: $file\n";
    } else {
        echo "Model already uses the traits: $file\n";
    }
}

echo "\nModel merging completed.\n";
