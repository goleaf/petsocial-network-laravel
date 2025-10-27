<?php

use App\Http\Livewire\Account\Analytics;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function Pest\Laravel\actingAs;

/**
 * Account analytics access scenarios driven by RBAC permissions.
 */
describe('Account analytics access control', function () {
    it('allows members with self analytics permission to load the dashboard', function () {
        // Preserve the original configuration so it can be restored after the test run.
        $originalRoles = config('access.roles');

        // Ensure the standard user role includes self analytics access during the scenario.
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        // Create a member and authenticate them to exercise the Livewire component.
        $member = User::factory()->create([
            'role' => 'user',
        ]);
        actingAs($member);

        $component = \Mockery::mock(Analytics::class)->makePartial();
        $component->shouldAllowMockingProtectedMethods();
        $component->shouldReceive('loadAnalytics')->once()->with($member);

        $component->mount();
        expect($component->overview)->toBeArray();

        // Restore the access configuration for isolation across subsequent tests.
        config(['access.roles' => $originalRoles]);
        \Mockery::close();
    });

    it('blocks members lacking analytics permissions from mounting the dashboard', function () {
        // Capture the default role definitions to reset once the scenario finishes.
        $originalRoles = config('access.roles');

        // Strip analytics permissions so the guard clause triggers.
        config(['access.roles.user.permissions' => ['profile.update', 'privacy.update']]);

        $member = User::factory()->create([
            'role' => 'user',
        ]);
        actingAs($member);

        $component = \Mockery::mock(Analytics::class)->makePartial();
        $component->shouldAllowMockingProtectedMethods();
        $component->shouldNotReceive('loadAnalytics');

        expect(fn () => $component->mount())->toThrow(HttpException::class);

        // Reinstate the original configuration after the assertion.
        config(['access.roles' => $originalRoles]);
        \Mockery::close();
    });

    it('aggregates analytics datasets and streams custom exports', function () {
        // Capture the default RBAC configuration so it can be restored after the scenario.
        $originalRoles = config('access.roles');

        // Ensure the member role grants analytics access for the duration of the test.
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        Carbon::setTestNow(Carbon::parse('2025-04-01 12:00:00'));

        // Seed a member along with an additional account for interaction data.
        $member = User::factory()->create([
            'role' => 'user',
        ]);
        $peer = User::factory()->create();
        actingAs($member);

        // Insert recent posts so the analytics snapshot has content to summarise.
        $postOneId = DB::table('posts')->insertGetId([
            'user_id' => $member->id,
            'content' => 'Morning walk update',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);
        $postTwoId = DB::table('posts')->insertGetId([
            'user_id' => $member->id,
            'content' => 'Evening training recap',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $postOne = Post::find($postOneId);
        $postTwo = Post::find($postTwoId);

        // Record engagement inputs from the member and the supporting peer account.
        DB::table('comments')->insert([
            'user_id' => $member->id,
            'post_id' => $postOne->id,
            'content' => 'Loved this walk!',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        Reaction::query()->create([
            'user_id' => $member->id,
            'post_id' => $postTwo->id,
            'type' => 'like',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        Reaction::query()->create([
            'user_id' => $peer->id,
            'post_id' => $postOne->id,
            'type' => 'love',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        Share::query()->create([
            'user_id' => $peer->id,
            'post_id' => $postOne->id,
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        Share::query()->create([
            'user_id' => $member->id,
            'post_id' => $postTwo->id,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        // Populate friendship and follower relationships for growth reporting.
        Friendship::query()->create([
            'sender_id' => $member->id,
            'recipient_id' => $peer->id,
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => Carbon::now()->subDays(3),
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        DB::table('follows')->insert([
            'follower_id' => $peer->id,
            'followed_id' => $member->id,
            'notify' => 0,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        Cache::flush();

        try {
            $component = app(Analytics::class);
            $component->mount();

            expect($component->overview)->toMatchArray([
                'posts' => 2,
                'comments' => 1,
                'reactions_made' => 1,
                'reactions_received' => 1,
                'shares_received' => 1,
            ]);

            expect($component->behaviorAnalysis['reactions_received_per_post'])->toBe(0.5);
            expect($component->activityPatterns['peak_hour_label'])->toBeString();
            expect($component->growthTracking)->not()->toBeEmpty();
            expect($component->reportSummary['date_range_days'])->toBeGreaterThan(0);

            $response = $component->exportReport();
            expect($response)->toBeInstanceOf(StreamedResponse::class);

            ob_start();
            $response->sendContent();
            $csv = ob_get_clean();

            expect($csv)->toContain('posts');
            expect($csv)->toContain('2');
        } finally {
            // Restore shared state for isolation across the remainder of the suite.
            config(['access.roles' => $originalRoles]);
            Carbon::setTestNow();
            Cache::flush();
        }
    });

    it('confirms the analytics blade view remains available for the dashboard', function () {
        // Guard against accidental template removals by explicitly checking the blade exists.
        expect(view()->exists('livewire.account.analytics'))->toBeTrue();
    });
});
