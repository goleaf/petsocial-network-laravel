<?php

use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View as ViewFactory;

/**
 * Filament-oriented test confirming the Livewire view renders cleanly for dashboard embedding.
 */
describe('Account analytics Filament compatibility', function () {
    it('renders the analytics view with the expected layout bindings', function () {
        // Provide deterministic analytics data exactly as the Livewire component would expose to Filament.
        $view = ViewFactory::make('livewire.account.analytics', [
            'overview' => [
                'posts' => 3,
                'comments' => 5,
                'reactions_made' => 8,
                'reactions_received' => 12,
                'shares_received' => 2,
            ],
            'engagementTrend' => [
                'Apr 2025' => ['posts' => 1, 'reactions_received' => 4, 'shares' => 1],
            ],
            'friendInsights' => [
                'total' => 10,
                'new_last_30_days' => 2,
                'pending' => 1,
                'blocked' => 0,
            ],
            'activityPatterns' => [
                'by_weekday' => ['Monday' => 2],
                'by_hour' => [0 => 1],
                'peak_hour_label' => '00:00',
            ],
            'behaviorAnalysis' => [
                'avg_posts_per_day' => 0.5,
                'reactions_given_per_post' => 2.6,
                'comments_per_post' => 1.0,
                'reactions_received_per_post' => 4.0,
                'shares_per_post' => 0.6,
            ],
            'growthTracking' => [
                'Apr 2025' => ['new_friends' => 1, 'new_followers' => 3],
            ],
            'reportSummary' => [
                'posts' => 3,
                'date_range_days' => 30,
                'top_post_reactions' => 6,
            ],
            'topPosts' => new Collection([
                (object) [
                    'content' => 'Evening training recap',
                    'reactions_count' => 6,
                    'comments_count' => 3,
                    'shares_count' => 2,
                    'created_at' => now(),
                ],
            ]),
        ]);

        // The view factory should yield an Illuminate view ready for Filament embedding.
        expect($view)->toBeInstanceOf(View::class);
        expect($view->getName())->toBe('livewire.account.analytics');

        // Rendering the view should include the analytics filter copy used across dashboards.
        $output = $view->render();
        expect($output)->toContain('Filter analytics by date range');
        expect($output)->toContain('Evening training recap');
    });
});
