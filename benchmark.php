<?php

/**
 * Pet Social Network Performance Benchmark Tool
 * 
 * This script measures the performance of key operations before and after optimization.
 * Run this script from the command line: php benchmark.php
 */

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceBenchmark
{
    protected $results = [];
    protected $iterations = 10;
    protected $userId;
    protected $petId;

    public function __construct()
    {
        // Disable query log to prevent memory issues
        DB::disableQueryLog();
        
        // Get a random user and pet for testing
        $this->userId = DB::table('users')->inRandomOrder()->value('id');
        $this->petId = DB::table('pets')->inRandomOrder()->value('id');
        
        echo "Starting benchmark with User ID: {$this->userId}, Pet ID: {$this->petId}\n";
    }

    public function runBenchmarks()
    {
        $this->benchmarkFriendsList();
        $this->benchmarkFriendButton();
        $this->benchmarkActivityLog();
        $this->benchmarkAnalytics();
        $this->benchmarkQueryCount();
        
        $this->displayResults();
        $this->saveResultsToFile();
    }

    protected function benchmarkFriendsList()
    {
        echo "Benchmarking Friends List...\n";
        
        // Clear cache to ensure fair comparison
        Cache::flush();
        
        $queryCount = 0;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            DB::enableQueryLog();
            
            // Create and render the component
            $component = app(\App\Http\Livewire\Common\FriendsList::class, [
                'entityType' => 'user',
                'entityId' => $this->userId
            ]);
            
            // Force loading of friends
            $friends = $component->getFriends();
            
            $queries = DB::getQueryLog();
            $queryCount += count($queries);
            
            DB::disableQueryLog();
        }
        
        $endTime = microtime(true);
        $avgTime = ($endTime - $startTime) / $this->iterations * 1000; // in ms
        $avgQueries = $queryCount / $this->iterations;
        
        $this->results['FriendsList'] = [
            'time' => round($avgTime, 2),
            'queries' => round($avgQueries, 2)
        ];
    }

    protected function benchmarkFriendButton()
    {
        echo "Benchmarking Friend Button...\n";
        
        // Clear cache to ensure fair comparison
        Cache::flush();
        
        $queryCount = 0;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            DB::enableQueryLog();
            
            // Create and render the component
            $component = app(\App\Http\Livewire\Common\FriendButton::class, [
                'entityType' => 'user',
                'entityId' => $this->userId,
                'targetType' => 'user',
                'targetId' => $this->userId + 1 // Another user
            ]);
            
            // Force checking friendship status
            $status = $component->getFriendshipStatus();
            
            $queries = DB::getQueryLog();
            $queryCount += count($queries);
            
            DB::disableQueryLog();
        }
        
        $endTime = microtime(true);
        $avgTime = ($endTime - $startTime) / $this->iterations * 1000; // in ms
        $avgQueries = $queryCount / $this->iterations;
        
        $this->results['FriendButton'] = [
            'time' => round($avgTime, 2),
            'queries' => round($avgQueries, 2)
        ];
    }

    protected function benchmarkActivityLog()
    {
        echo "Benchmarking Activity Log...\n";
        
        // Clear cache to ensure fair comparison
        Cache::flush();
        
        $queryCount = 0;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            DB::enableQueryLog();
            
            // Create and render the component
            $component = app(\App\Http\Livewire\Common\ActivityLog::class, [
                'entityType' => 'user',
                'entityId' => $this->userId
            ]);
            
            // Force loading of activities
            $activities = $component->getActivities();
            
            $queries = DB::getQueryLog();
            $queryCount += count($queries);
            
            DB::disableQueryLog();
        }
        
        $endTime = microtime(true);
        $avgTime = ($endTime - $startTime) / $this->iterations * 1000; // in ms
        $avgQueries = $queryCount / $this->iterations;
        
        $this->results['ActivityLog'] = [
            'time' => round($avgTime, 2),
            'queries' => round($avgQueries, 2)
        ];
    }

    protected function benchmarkAnalytics()
    {
        echo "Benchmarking Analytics...\n";
        
        // Clear cache to ensure fair comparison
        Cache::flush();
        
        $queryCount = 0;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            DB::enableQueryLog();
            
            // Create and render the component
            $component = app(\App\Http\Livewire\Common\FriendAnalytics::class, [
                'entityType' => 'user',
                'entityId' => $this->userId
            ]);
            
            // Force loading of analytics
            $component->loadAnalytics();
            
            $queries = DB::getQueryLog();
            $queryCount += count($queries);
            
            DB::disableQueryLog();
        }
        
        $endTime = microtime(true);
        $avgTime = ($endTime - $startTime) / $this->iterations * 1000; // in ms
        $avgQueries = $queryCount / $this->iterations;
        
        $this->results['Analytics'] = [
            'time' => round($avgTime, 2),
            'queries' => round($avgQueries, 2)
        ];
    }

    protected function benchmarkQueryCount()
    {
        echo "Benchmarking Query Count for Common Operations...\n";
        
        // Clear cache to ensure fair comparison
        Cache::flush();
        
        DB::enableQueryLog();
        
        // Simulate a typical user session with multiple operations
        $friendsList = app(\App\Http\Livewire\Common\FriendsList::class, [
            'entityType' => 'user',
            'entityId' => $this->userId
        ]);
        $friends = $friendsList->getFriends();
        
        $activityLog = app(\App\Http\Livewire\Common\ActivityLog::class, [
            'entityType' => 'user',
            'entityId' => $this->userId
        ]);
        $activities = $activityLog->getActivities();
        
        $friendButton = app(\App\Http\Livewire\Common\FriendButton::class, [
            'entityType' => 'user',
            'entityId' => $this->userId,
            'targetType' => 'user',
            'targetId' => $this->userId + 1
        ]);
        $status = $friendButton->getFriendshipStatus();
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        DB::disableQueryLog();
        
        $this->results['TotalQueryCount'] = [
            'queries' => $queryCount
        ];
        
        // Now with cache
        Cache::flush(); // Clear first to simulate first load
        
        // First load - should populate cache
        DB::enableQueryLog();
        
        $friendsList->getFriends();
        $activityLog->getActivities();
        $friendButton->getFriendshipStatus();
        
        $queries = DB::getQueryLog();
        $firstLoadQueryCount = count($queries);
        
        DB::disableQueryLog();
        
        // Second load - should use cache
        DB::enableQueryLog();
        
        $friendsList->getFriends();
        $activityLog->getActivities();
        $friendButton->getFriendshipStatus();
        
        $queries = DB::getQueryLog();
        $secondLoadQueryCount = count($queries);
        
        DB::disableQueryLog();
        
        $this->results['CachedQueryCount'] = [
            'first_load' => $firstLoadQueryCount,
            'second_load' => $secondLoadQueryCount,
            'reduction' => round(($firstLoadQueryCount - $secondLoadQueryCount) / $firstLoadQueryCount * 100, 2) . '%'
        ];
    }

    protected function displayResults()
    {
        echo "\n==== BENCHMARK RESULTS ====\n";
        
        foreach ($this->results as $test => $result) {
            echo "\n$test:\n";
            
            foreach ($result as $metric => $value) {
                echo "  $metric: $value\n";
            }
        }
        
        echo "\n==========================\n";
    }

    protected function saveResultsToFile()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = __DIR__ . "/benchmark_results_{$timestamp}.json";
        
        file_put_contents($filename, json_encode($this->results, JSON_PRETTY_PRINT));
        
        echo "Results saved to $filename\n";
    }
}

// Run the benchmark
$benchmark = new PerformanceBenchmark();
$benchmark->runBenchmarks();
