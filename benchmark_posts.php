<?php

/**
 * Post and Comment Components Benchmark Script
 * 
 * This script measures the performance of the optimized post and comment components
 * compared to the original implementations.
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Livewire\Content\CreatePost;
use App\Http\Livewire\Content\CommentSection;
use App\Http\Livewire\Common\PostManager;
use App\Http\Livewire\Common\CommentManager;
use App\Models\Post;
use App\Models\User;
use App\Models\Pet;

// Helper function to measure execution time and query count
function benchmark($callback, $name) {
    // Clear cache before each test
    Cache::flush();
    
    // Reset query log
    DB::flushQueryLog();
    DB::enableQueryLog();
    
    $startTime = microtime(true);
    $result = $callback();
    $endTime = microtime(true);
    
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    $queryCount = count(DB::getQueryLog());
    $queryTime = array_reduce(DB::getQueryLog(), function($carry, $query) {
        return $carry + $query['time'];
    }, 0);
    
    echo "=== {$name} ===\n";
    echo "Execution Time: " . number_format($executionTime, 2) . " ms\n";
    echo "Query Count: {$queryCount}\n";
    echo "Total Query Time: " . number_format($queryTime, 2) . " ms\n";
    echo "\n";
    
    return [
        'execution_time' => $executionTime,
        'query_count' => $queryCount,
        'query_time' => $queryTime,
    ];
}

// Get test data
$user = User::first();
$pet = Pet::first();
$post = Post::first();

// Benchmark original post component
$originalPostResults = benchmark(function() use ($user) {
    $component = new CreatePost();
    $component->content = "Test post content";
    $component->tags = "test, benchmark";
    $component->save();
    return $component;
}, "Original Post Creation");

// Benchmark optimized post component
$optimizedPostResults = benchmark(function() use ($user) {
    $component = new PostManager(['entityType' => 'user', 'entityId' => $user->id]);
    $component->content = "Test post content";
    $component->tags = "test, benchmark";
    $component->save();
    return $component;
}, "Optimized Post Creation");

// Benchmark original comment component
$originalCommentResults = benchmark(function() use ($post) {
    $component = new CommentSection(['postId' => $post->id]);
    $component->content = "Test comment content";
    $component->save();
    return $component;
}, "Original Comment Addition");

// Benchmark optimized comment component
$optimizedCommentResults = benchmark(function() use ($post) {
    $component = new CommentManager(['postId' => $post->id]);
    $component->content = "Test comment content";
    $component->save();
    return $component;
}, "Optimized Comment Addition");

// Benchmark original post loading
$originalPostLoadResults = benchmark(function() use ($user) {
    // Simulate loading posts without caching
    return Post::where('user_id', $user->id)
        ->with(['user', 'pet', 'tags', 'comments'])
        ->latest()
        ->paginate(10);
}, "Original Post Feed Loading");

// Benchmark optimized post loading
$optimizedPostLoadResults = benchmark(function() use ($user) {
    $component = new PostManager(['entityType' => 'user', 'entityId' => $user->id]);
    return $component->getPosts();
}, "Optimized Post Feed Loading");

// Benchmark original comment loading
$originalCommentLoadResults = benchmark(function() use ($post) {
    $component = new CommentSection(['postId' => $post->id]);
    $component->loadComments();
    return $component->comments;
}, "Original Comment Section Loading");

// Benchmark optimized comment loading
$optimizedCommentLoadResults = benchmark(function() use ($post) {
    $component = new CommentManager(['postId' => $post->id]);
    return $component->getComments();
}, "Optimized Comment Section Loading");

// Calculate improvements
function calculateImprovement($original, $optimized, $metric = 'execution_time') {
    $improvement = (($original[$metric] - $optimized[$metric]) / $original[$metric]) * 100;
    return number_format($improvement, 2);
}

// Print summary
echo "=== PERFORMANCE IMPROVEMENT SUMMARY ===\n\n";

echo "Post Creation: " . calculateImprovement($originalPostResults, $optimizedPostResults) . "% faster\n";
echo "Comment Addition: " . calculateImprovement($originalCommentResults, $optimizedCommentResults) . "% faster\n";
echo "Post Feed Loading: " . calculateImprovement($originalPostLoadResults, $optimizedPostLoadResults) . "% faster\n";
echo "Comment Section Loading: " . calculateImprovement($originalCommentLoadResults, $optimizedCommentLoadResults) . "% faster\n";

echo "\n=== QUERY COUNT REDUCTION ===\n\n";

echo "Post Creation: " . calculateImprovement($originalPostResults, $optimizedPostResults, 'query_count') . "% reduction\n";
echo "Comment Addition: " . calculateImprovement($originalCommentResults, $optimizedCommentResults, 'query_count') . "% reduction\n";
echo "Post Feed Loading: " . calculateImprovement($originalPostLoadResults, $optimizedPostLoadResults, 'query_count') . "% reduction\n";
echo "Comment Section Loading: " . calculateImprovement($originalCommentLoadResults, $optimizedCommentLoadResults, 'query_count') . "% reduction\n";

// Export results to JSON for further analysis
$results = [
    'post_creation' => [
        'original' => $originalPostResults,
        'optimized' => $optimizedPostResults,
        'improvement' => calculateImprovement($originalPostResults, $optimizedPostResults),
    ],
    'comment_addition' => [
        'original' => $originalCommentResults,
        'optimized' => $optimizedCommentResults,
        'improvement' => calculateImprovement($originalCommentResults, $optimizedCommentResults),
    ],
    'post_feed_loading' => [
        'original' => $originalPostLoadResults,
        'optimized' => $optimizedPostLoadResults,
        'improvement' => calculateImprovement($originalPostLoadResults, $optimizedPostLoadResults),
    ],
    'comment_section_loading' => [
        'original' => $originalCommentLoadResults,
        'optimized' => $optimizedCommentLoadResults,
        'improvement' => calculateImprovement($originalCommentLoadResults, $optimizedCommentLoadResults),
    ],
];

file_put_contents(__DIR__.'/benchmark_results_posts.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nBenchmark results saved to benchmark_results_posts.json\n";
