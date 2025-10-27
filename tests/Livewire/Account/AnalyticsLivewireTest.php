<?php

use App\Http\Livewire\Account\Analytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire interaction tests ensure the component responds to reactive updates.
 */
describe('Account analytics Livewire interactions', function () {
    it('recomputes analytics when the start date is updated via Livewire', function () {
        // Preserve and later restore access control to avoid bleeding configuration into other suites.
        $originalRoles = config('access.roles');
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        Carbon::setTestNow(Carbon::parse('2025-04-01 10:00:00'));

        // Seed the authenticated member alongside a peer account for reaction data.
        $member = User::factory()->create(['role' => 'user']);
        $peer = User::factory()->create();
        actingAs($member);

        // Insert one recent and one older post so the narrowed range yields different totals.
        DB::table('posts')->insert([
            'user_id' => $member->id,
            'content' => 'Morning agility drills',
            'created_at' => Carbon::now()->subDays(12),
            'updated_at' => Carbon::now()->subDays(12),
        ]);

        $recentPostId = DB::table('posts')->insertGetId([
            'user_id' => $member->id,
            'content' => 'Evening fetch championship',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        DB::table('reactions')->insert([
            'user_id' => $peer->id,
            'post_id' => $recentPostId,
            'type' => 'like',
            'created_at' => Carbon::now()->subHours(2),
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        try {
            $component = Livewire::test(Analytics::class);

            // Baseline expectation includes the historic post in the 30 day default window.
            $component->assertSet('startDate', Carbon::now()->subDays(29)->toDateString());
            $initialOverview = $component->get('overview');
            expect($initialOverview['posts'])->toBe(2);

            // Simulate the member constraining the start date via the Livewire-powered input.
            $component->set('startDate', Carbon::now()->subDays(1)->toDateString());

            // Manually invoke the refresh hook to mimic Livewire dispatching a network round-trip.
            $component->call('refreshAnalytics');

            $refreshedOverview = $component->get('overview');

            // The recalculated snapshot should only include the post within the shortened window.
            expect($refreshedOverview['posts'])->toBe(1);
            expect($component->get('topPosts')->first()->id)->toBe($recentPostId);
        } finally {
            // Reset shared state after the assertion block completes.
            config(['access.roles' => $originalRoles]);
            Carbon::setTestNow();
        }
    });

    it('renders the expected analytics blade while exercising Livewire assertions', function () {
        // Instantiate the component without authentication to focus purely on template wiring.
        $component = Livewire::test(Analytics::class);

        // Confirm the Livewire harness references the dedicated analytics blade view.
        $component->assertViewIs('livewire.account.analytics');
    });
});
