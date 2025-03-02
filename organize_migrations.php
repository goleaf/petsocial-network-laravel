<?php

// Script to organize migrations by table name
$migrationsPath = __DIR__ . '/database/migrations';
$files = scandir($migrationsPath);
$tableToMigrations = [];
$duplicates = [];

// Skip . and ..
$files = array_filter($files, function($file) {
    return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

foreach ($files as $file) {
    $filePath = $migrationsPath . '/' . $file;
    $content = file_get_contents($filePath);
    
    // Extract table names from Schema::create or Schema::table
    preg_match_all('/Schema::(create|table)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
    
    if (!empty($matches[2])) {
        foreach ($matches[2] as $table) {
            if (!isset($tableToMigrations[$table])) {
                $tableToMigrations[$table] = [];
            }
            $tableToMigrations[$table][] = $file;
            
            // If we have more than one migration for a table, mark it as a duplicate
            if (count($tableToMigrations[$table]) > 1) {
                $duplicates[$table] = $tableToMigrations[$table];
            }
        }
    }
}

// Output the results
echo "Tables with multiple migrations:\n";
foreach ($duplicates as $table => $migrations) {
    echo "Table: $table\n";
    foreach ($migrations as $migration) {
        echo "  - $migration\n";
    }
    echo "\n";
}

// Now create a list of tables to create merged migrations for
$tablesToMerge = array_keys($duplicates);
echo "Tables to merge: " . implode(', ', $tablesToMerge) . "\n";

// Create a directory for merged migrations if it doesn't exist
$mergedPath = $migrationsPath . '/merged';
if (!is_dir($mergedPath)) {
    mkdir($mergedPath);
}

// For each table with duplicates, create a merged migration
foreach ($duplicates as $table => $migrations) {
    $timestamp = date('Y_m_d_His');
    $className = 'Merged' . ucfirst(str_replace('_', '', ucwords($table, '_'))) . 'Migration';
    $fileName = "{$timestamp}_merged_{$table}_migration.php";
    $filePath = $mergedPath . '/' . $fileName;
    
    // Start building the merged migration
    $mergedContent = "<?php\n\n";
    $mergedContent .= "use Illuminate\\Database\\Migrations\\Migration;\n";
    $mergedContent .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
    $mergedContent .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
    $mergedContent .= "class {$className} extends Migration\n{\n";
    $mergedContent .= "    /**\n     * Run the migrations.\n     *\n     * @return void\n     */\n";
    $mergedContent .= "    public function up()\n    {\n";
    
    // Add code to drop the table if it exists
    $mergedContent .= "        // Drop the table if it exists\n";
    $mergedContent .= "        Schema::dropIfExists('{$table}');\n\n";
    
    // Extract the create table code from the first migration
    $firstMigration = $migrations[0];
    $firstMigrationContent = file_get_contents($migrationsPath . '/' . $firstMigration);
    
    // Extract the Schema::create block
    preg_match('/Schema::create\s*\(\s*[\'"]' . preg_quote($table, '/') . '[\'"],\s*function\s*\(Blueprint\s*\$table\)\s*{(.*?)}\);/s', $firstMigrationContent, $createMatches);
    
    if (!empty($createMatches[1])) {
        $mergedContent .= "        // Create the table\n";
        $mergedContent .= "        Schema::create('{$table}', function (Blueprint \$table) {\n";
        $mergedContent .= trim($createMatches[1]) . "\n";
        $mergedContent .= "        });\n\n";
    }
    
    // Extract additional columns from other migrations
    for ($i = 1; $i < count($migrations); $i++) {
        $migration = $migrations[$i];
        $migrationContent = file_get_contents($migrationsPath . '/' . $migration);
        
        // Extract the Schema::table block
        preg_match('/Schema::table\s*\(\s*[\'"]' . preg_quote($table, '/') . '[\'"],\s*function\s*\(Blueprint\s*\$table\)\s*{(.*?)}\);/s', $migrationContent, $tableMatches);
        
        if (!empty($tableMatches[1])) {
            $mergedContent .= "        // Add additional columns from {$migration}\n";
            $mergedContent .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";
            $mergedContent .= trim($tableMatches[1]) . "\n";
            $mergedContent .= "        });\n\n";
        }
    }
    
    $mergedContent .= "    }\n\n";
    $mergedContent .= "    /**\n     * Reverse the migrations.\n     *\n     * @return void\n     */\n";
    $mergedContent .= "    public function down()\n    {\n";
    $mergedContent .= "        Schema::dropIfExists('{$table}');\n";
    $mergedContent .= "    }\n";
    $mergedContent .= "}\n";
    
    // Write the merged migration to file
    file_put_contents($filePath, $mergedContent);
    echo "Created merged migration for {$table}: {$fileName}\n";
}

echo "\nDone organizing migrations.\n";
