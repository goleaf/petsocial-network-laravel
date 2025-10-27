<?php

use App\Http\Livewire\Account\Analytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage for date filter refresh logic on the analytics dashboard.
 */
describe('Account analytics refresh flow', function () {
    it('rebuilds metrics after narrowing the reporting window', function () {
        // Capture and later restore the access control configuration for isolation.
        $originalRoles = config('access.roles');

        // Guarantee the member role includes the self analytics permission while the test runs.
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        Carbon::setTestNow(Carbon::parse('2025-04-01 09:00:00'));

        // Seed a member with a supporting peer to generate engagement signals.
        $member = User::factory()->create([
            'role' => 'user',
        ]);
        $peer = User::factory()->create();
        actingAs($member);

        // Insert two posts that straddle the eventual narrowed analytics range.
        $recentPostId = DB::table('posts')->insertGetId([
            'user_id' => $member->id,
            'content' => 'Training montage recap',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('posts')->insert([
            'user_id' => $member->id,
            'content' => 'Throwback adventure log',
            'created_at' => Carbon::now()->subDays(14),
            'updated_at' => Carbon::now()->subDays(14),
        ]);

        // React to the recent post so top-post calculations update alongside the refresh.
        DB::table('reactions')->insert([
            'user_id' => $peer->id,
            'post_id' => $recentPostId,
            'type' => 'love',
            'created_at' => Carbon::now()->subHours(6),
            'updated_at' => Carbon::now()->subHours(6),
        ]);

        try {
            $component = app(Analytics::class);
            $component->mount();

            // Confirm the baseline overview includes both seeded posts before the range adjustment.
            expect($component->overview['posts'])->toBe(2);

            // Narrow the filter to the last 48 hours and force the component to rebuild its datasets.
            $component->startDate = Carbon::now()->subDays(1)->toDateString();
            $component->endDate = Carbon::now()->toDateString();
            $component->refreshAnalytics();

            // Only the recent post should remain within scope after the refresh.
            expect($component->overview['posts'])->toBe(1);
            expect($component->topPosts->first()->id)->toBe($recentPostId);
        } finally {
            // Restore shared state and timestamps to avoid cross-test pollution.
            config(['access.roles' => $originalRoles]);
            Carbon::setTestNow();
        }
    });
});
