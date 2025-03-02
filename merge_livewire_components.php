<?php

// Script to merge duplicate Livewire components
$livewirePath = __DIR__ . '/app/Http/Livewire';
$commonPath = $livewirePath . '/Common';

// Create the Common directory if it doesn't exist
if (!is_dir($commonPath)) {
    mkdir($commonPath, 0755, true);
}

// Define component pairs to merge
$componentPairs = [
    [
        'source1' => $livewirePath . '/Pet/FriendButton.php',
        'source2' => $livewirePath . '/Social/Friend/Button.php',
        'target' => $commonPath . '/FriendButton.php',
        'className' => 'FriendButton',
        'namespace' => 'App\\Http\\Livewire\\Common'
    ],
    [
        'source1' => $livewirePath . '/Pet/FriendsList.php',
        'source2' => $livewirePath . '/Social/Friend/List.php',
        'target' => $commonPath . '/FriendsList.php',
        'className' => 'FriendsList',
        'namespace' => 'App\\Http\\Livewire\\Common'
    ],
    [
        'source1' => $livewirePath . '/Pet/FriendHub.php',
        'source2' => $livewirePath . '/Social/Friend/Dashboard.php',
        'target' => $commonPath . '/FriendHub.php',
        'className' => 'FriendHub',
        'namespace' => 'App\\Http\\Livewire\\Common'
    ],
    [
        'source1' => $livewirePath . '/Pet/FriendFinder.php',
        'source2' => $livewirePath . '/Social/Friend/Finder.php',
        'target' => $commonPath . '/FriendFinder.php',
        'className' => 'FriendFinder',
        'namespace' => 'App\\Http\\Livewire\\Common'
    ],
    [
        'source1' => $livewirePath . '/Pet/ActivityLog.php',
        'source2' => $livewirePath . '/Social/Friend/Activity.php',
        'target' => $commonPath . '/ActivityLog.php',
        'className' => 'ActivityLog',
        'namespace' => 'App\\Http\\Livewire\\Common'
    ]
];

// Removed Pet/FriendAnalytics.php as it's been consolidated into Common/FriendAnalytics.php

// Function to merge two components
function mergeComponents($source1, $source2, $target, $className, $namespace) {
    if (!file_exists($source1) || !file_exists($source2)) {
        echo "One or both source files don't exist: $source1, $source2\n";
        return false;
    }
    
    $content1 = file_get_contents($source1);
    $content2 = file_get_contents($source2);
    
    // Extract class content
    preg_match('/class\s+\w+.*{(.*)}$/s', $content1, $matches1);
    preg_match('/class\s+\w+.*{(.*)}$/s', $content2, $matches2);
    
    if (empty($matches1[1]) || empty($matches2[1])) {
        echo "Could not extract class content from one or both files\n";
        return false;
    }
    
    $classContent1 = $matches1[1];
    $classContent2 = $matches2[1];
    
    // Extract properties and methods
    preg_match_all('/\s*(public|protected|private)\s+(?:\$|\function)\w+.*?(?:;|\{.*?\})/s', $classContent1, $elements1);
    preg_match_all('/\s*(public|protected|private)\s+(?:\$|\function)\w+.*?(?:;|\{.*?\})/s', $classContent2, $elements2);
    
    $properties = [];
    $methods = [];
    
    // Process elements from first component
    foreach ($elements1[0] as $element) {
        if (strpos($element, 'function') !== false) {
            preg_match('/function\s+(\w+)/', $element, $methodName);
            if (!empty($methodName[1])) {
                $methods[$methodName[1]] = $element;
            }
        } else {
            preg_match('/\$(\w+)/', $element, $propertyName);
            if (!empty($propertyName[1])) {
                $properties[$propertyName[1]] = $element;
            }
        }
    }
    
    // Process elements from second component
    foreach ($elements2[0] as $element) {
        if (strpos($element, 'function') !== false) {
            preg_match('/function\s+(\w+)/', $element, $methodName);
            if (!empty($methodName[1]) && !isset($methods[$methodName[1]])) {
                $methods[$methodName[1]] = $element;
            }
        } else {
            preg_match('/\$(\w+)/', $element, $propertyName);
            if (!empty($propertyName[1]) && !isset($properties[$propertyName[1]])) {
                $properties[$propertyName[1]] = $element;
            }
        }
    }
    
    // Create the merged component
    $mergedContent = "<?php\n\n";
    $mergedContent .= "namespace $namespace;\n\n";
    $mergedContent .= "use Livewire\\Component;\n";
    $mergedContent .= "use App\\Models\\User;\n";
    $mergedContent .= "use App\\Models\\Pet;\n";
    $mergedContent .= "use App\\Models\\Friendship;\n";
    $mergedContent .= "use App\\Models\\PetFriendship;\n";
    $mergedContent .= "use Illuminate\\Support\\Facades\\Auth;\n\n";
    
    $mergedContent .= "class $className extends Component\n{\n";
    
    // Add trait for polymorphic handling
    $mergedContent .= "    /**\n";
    $mergedContent .= "     * Trait to handle both User and Pet friendships\n";
    $mergedContent .= "     */\n";
    $mergedContent .= "    protected \$entityType = null; // 'user' or 'pet'\n";
    $mergedContent .= "    protected \$entityId = null;\n\n";
    
    $mergedContent .= "    /**\n";
    $mergedContent .= "     * Set the entity type and ID\n";
    $mergedContent .= "     */\n";
    $mergedContent .= "    public function setEntity(string \$type, int \$id)\n";
    $mergedContent .= "    {\n";
    $mergedContent .= "        \$this->entityType = \$type;\n";
    $mergedContent .= "        \$this->entityId = \$id;\n";
    $mergedContent .= "    }\n\n";
    
    $mergedContent .= "    /**\n";
    $mergedContent .= "     * Get the appropriate model based on entity type\n";
    $mergedContent .= "     */\n";
    $mergedContent .= "    protected function getModel()\n";
    $mergedContent .= "    {\n";
    $mergedContent .= "        return \$this->entityType === 'pet' ? Pet::find(\$this->entityId) : User::find(\$this->entityId);\n";
    $mergedContent .= "    }\n\n";
    
    $mergedContent .= "    /**\n";
    $mergedContent .= "     * Get the appropriate friendship model based on entity type\n";
    $mergedContent .= "     */\n";
    $mergedContent .= "    protected function getFriendshipModel()\n";
    $mergedContent .= "    {\n";
    $mergedContent .= "        return \$this->entityType === 'pet' ? PetFriendship::class : Friendship::class;\n";
    $mergedContent .= "    }\n\n";
    
    // Add properties
    foreach ($properties as $property) {
        $mergedContent .= "    $property\n\n";
    }
    
    // Add methods
    foreach ($methods as $method) {
        $mergedContent .= "    $method\n\n";
    }
    
    // Add render method if not already included
    if (!isset($methods['render'])) {
        $mergedContent .= "    /**\n";
        $mergedContent .= "     * Render the component\n";
        $mergedContent .= "     */\n";
        $mergedContent .= "    public function render()\n";
        $mergedContent .= "    {\n";
        $mergedContent .= "        return view('livewire.common." . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $className)) . "');\n";
        $mergedContent .= "    }\n";
    }
    
    $mergedContent .= "}\n";
    
    // Write the merged component to file
    file_put_contents($target, $mergedContent);
    echo "Created merged component: $target\n";
    
    return true;
}

// Merge all component pairs
foreach ($componentPairs as $pair) {
    mergeComponents($pair['source1'], $pair['source2'], $pair['target'], $pair['className'], $pair['namespace']);
}

echo "\nLivewire component merging completed.\n";
