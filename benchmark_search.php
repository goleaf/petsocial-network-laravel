<?php

require __DIR__.'/vendor/autoload.php';

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

// Bootstrap the Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear cache before benchmarking
Cache::flush();

// Login as a random user for testing
$user = User::inRandomOrder()->first();
Auth::login($user);

echo "Benchmarking search functionality...\n";
echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Common search queries to test
$searchQueries = [
    'dog',
    'cat',
    'vacation',
    'food',
    'park'
];

// Benchmark UnifiedSearch (first run - no cache)
echo "=== UnifiedSearch Component (First Run) ===\n";
$totalFirstRunTime = 0;
$firstRunQueries = 0;

foreach ($searchQueries as $query) {
    // Create component instance
    $unifiedSearch = new UnifiedSearch();
    $unifiedSearch->query = $query;
    $unifiedSearch->type = 'all'; // Test with all types
    
    // Clear query counter
    \DB::enableQueryLog();
    
    // Benchmark
    $startTime = microtime(true);
    $unifiedSearch->render();
    $endTime = microtime(true);
    
    // Get query count
    $queryLog = \DB::getQueryLog();
    $queryCount = count($queryLog);
    \DB::flushQueryLog();
    
    // Calculate time
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    $totalFirstRunTime += $executionTime;
    $firstRunQueries += $queryCount;
    
    echo "Query: '{$query}' - Time: " . number_format($executionTime, 2) . "ms - Queries: {$queryCount}\n";
}

$avgFirstRunTime = $totalFirstRunTime / count($searchQueries);
$avgFirstRunQueries = $firstRunQueries / count($searchQueries);

echo "Average Time: " . number_format($avgFirstRunTime, 2) . "ms\n";
echo "Average Queries: " . number_format($avgFirstRunQueries, 2) . "\n\n";

// Run a second time to test caching
echo "=== UnifiedSearch Component (With Caching) ===\n";
$totalCachedTime = 0;
$cachedQueries = 0;

foreach ($searchQueries as $query) {
    // Create component instance
    $unifiedSearch = new UnifiedSearch();
    $unifiedSearch->query = $query;
    $unifiedSearch->type = 'all'; // Test with all types
    
    // Clear query counter
    \DB::enableQueryLog();
    
    // Benchmark
    $startTime = microtime(true);
    $unifiedSearch->render();
    $endTime = microtime(true);
    
    // Get query count
    $queryLog = \DB::getQueryLog();
    $queryCount = count($queryLog);
    \DB::flushQueryLog();
    
    // Calculate time
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    $totalCachedTime += $executionTime;
    $cachedQueries += $queryCount;
    
    echo "Query: '{$query}' - Time: " . number_format($executionTime, 2) . "ms - Queries: {$queryCount}\n";
}

$avgCachedTime = $totalCachedTime / count($searchQueries);
$avgCachedQueries = $cachedQueries / count($searchQueries);

echo "Average Time: " . number_format($avgCachedTime, 2) . "ms\n";
echo "Average Queries: " . number_format($avgCachedQueries, 2) . "\n\n";

// Performance comparison
$timeImprovement = (($avgFirstRunTime - $avgCachedTime) / $avgFirstRunTime) * 100;
$queryImprovement = (($avgFirstRunQueries - $avgCachedQueries) / $avgFirstRunQueries) * 100;

echo "=== Performance Comparison ===\n";
echo "Time Improvement with Caching: " . number_format($timeImprovement, 2) . "%\n";
echo "Query Reduction with Caching: " . number_format($queryImprovement, 2) . "%\n";

// Test specific entity type searches
$entityTypes = ['posts', 'users', 'pets', 'tags'];

echo "\n=== Entity-Specific Search Performance ===\n";

foreach ($entityTypes as $type) {
    // Create component instance
    $unifiedSearch = new UnifiedSearch();
    $unifiedSearch->query = $searchQueries[0]; // Use the first query
    $unifiedSearch->type = $type;
    
    // Clear query counter
    \DB::enableQueryLog();
    
    // Benchmark
    $startTime = microtime(true);
    $unifiedSearch->render();
    $endTime = microtime(true);
    
    // Get query count
    $queryLog = \DB::getQueryLog();
    $queryCount = count($queryLog);
    \DB::flushQueryLog();
    
    // Calculate time
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    
    echo "Type: '{$type}' - Time: " . number_format($executionTime, 2) . "ms - Queries: {$queryCount}\n";
}

echo "\nBenchmarking completed!\n";
