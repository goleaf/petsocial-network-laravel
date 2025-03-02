<?php

// Script to fix duplicate columns in merged migrations
$mergedPath = __DIR__ . '/database/migrations/merged';
$files = scandir($mergedPath);

// Skip . and ..
$files = array_filter($files, function($file) {
    return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

foreach ($files as $file) {
    $filePath = $mergedPath . '/' . $file;
    $content = file_get_contents($filePath);
    
    // Find all Schema::table blocks
    preg_match_all('/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"],\s*function\s*\(Blueprint\s*\$table\)\s*{(.*?)}\);/s', $content, $matches, PREG_SET_ORDER);
    
    if (!empty($matches)) {
        $tableName = $matches[0][1];
        echo "Processing table: $tableName in file: $file\n";
        
        // Group by table name
        $tableBlocks = [];
        foreach ($matches as $match) {
            $table = $match[1];
            $block = $match[2];
            
            if (!isset($tableBlocks[$table])) {
                $tableBlocks[$table] = [];
            }
            
            $tableBlocks[$table][] = $block;
        }
        
        // Process each table
        foreach ($tableBlocks as $table => $blocks) {
            // Extract column definitions
            $columns = [];
            $processedBlocks = [];
            
            foreach ($blocks as $block) {
                // Find all column definitions
                preg_match_all('/\$table->([^(]+)\(([^)]*)\)([^;]*);/m', $block, $columnMatches, PREG_SET_ORDER);
                
                $processedBlock = $block;
                $duplicates = [];
                
                foreach ($columnMatches as $columnMatch) {
                    $columnType = $columnMatch[1];
                    $columnName = trim($columnMatch[2], '\'"');
                    
                    // If column name contains a comma (like in foreignId), extract the first part
                    if (strpos($columnName, ',') !== false) {
                        $columnName = explode(',', $columnName)[0];
                        $columnName = trim($columnName, '\'"');
                    }
                    
                    // Check if column already exists
                    if (isset($columns[$columnName])) {
                        echo "  Found duplicate column: $columnName\n";
                        $duplicates[] = $columnMatch[0];
                    } else {
                        $columns[$columnName] = true;
                    }
                }
                
                // Remove duplicate columns from the block
                foreach ($duplicates as $duplicate) {
                    $processedBlock = str_replace($duplicate, '// Removed duplicate: ' . $duplicate, $processedBlock);
                }
                
                $processedBlocks[] = $processedBlock;
            }
            
            // Replace the blocks in the content
            for ($i = 0; $i < count($blocks); $i++) {
                $content = str_replace($blocks[$i], $processedBlocks[$i], $content);
            }
        }
        
        // Write the updated content back to the file
        file_put_contents($filePath, $content);
        echo "Updated file: $filePath\n";
    }
}

echo "\nMigration fixes completed.\n";
