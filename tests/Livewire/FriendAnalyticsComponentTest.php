<?php

use App\Http\Livewire\Common\Friend\Analytics;
use App\Models\Friendship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire-level coverage that inspects the computed datasets exposed by the
 * friend analytics component.
 */
it('computes comprehensive analytics datasets for a member', function () {
    // Anchor the test window so relative date calculations remain predictable.
    Carbon::setTestNow(Carbon::parse('2025-03-01 12:00:00'));
    Cache::flush();

    // Seed the primary member and related accounts that will populate the
    // various friendship states surfaced by the analytics component.
    $member = User::factory()->create();
    $friendOne = User::factory()->create(['name' => 'Friend One']);
    $friendTwo = User::factory()->create(['name' => 'Friend Two']);
    $friendThree = User::factory()->create(['name' => 'Friend Three']);
    $pendingRecipient = User::factory()->create();
    $pendingSender = User::factory()->create();
    $blockedUser = User::factory()->create();

    actingAs($member);

    // Accepted friendship with a recent acceptance to influence the 30-day
    // counts and the average acceptance time calculations.
    $recentCreatedAt = Carbon::now()->subDays(20);
    $recentAcceptedAt = Carbon::now()->subDays(10);

    $recentFriendship = Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $friendOne->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'category' => 'Family',
        'accepted_at' => $recentAcceptedAt,
    ]);

    Friendship::withoutTimestamps(function () use ($recentFriendship, $recentCreatedAt, $recentAcceptedAt) {
        $recentFriendship->forceFill([
            'created_at' => $recentCreatedAt,
            'updated_at' => $recentAcceptedAt,
        ])->save();
    });

    // Older accepted friendship that contributes to historical trend buckets
    // while leaving the category blank to exercise the unknown label fallback.
    $historicCreatedAt = Carbon::now()->subDays(80);
    $historicAcceptedAt = Carbon::now()->subDays(70);

    $historicFriendship = Friendship::query()->create([
        'sender_id' => $friendTwo->id,
        'recipient_id' => $member->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'category' => null,
        'accepted_at' => $historicAcceptedAt,
    ]);

    Friendship::withoutTimestamps(function () use ($historicFriendship, $historicCreatedAt, $historicAcceptedAt) {
        $historicFriendship->forceFill([
            'created_at' => $historicCreatedAt,
            'updated_at' => $historicAcceptedAt,
        ])->save();
    });

    // Recently created friendship without an accepted timestamp to ensure the
    // analytics fall back to the request creation time for trend calculations.
    $recentRequestCreatedAt = Carbon::now()->subDays(7);

    $recentUnaccepted = Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $friendThree->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'category' => 'Gym Buddies',
        'accepted_at' => null,
    ]);

    Friendship::withoutTimestamps(function () use ($recentUnaccepted, $recentRequestCreatedAt) {
        $recentUnaccepted->forceFill([
            'created_at' => $recentRequestCreatedAt,
            'updated_at' => $recentRequestCreatedAt,
        ])->save();
    });

    // Pending requests from both the sender and receiver perspectives.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $pendingRecipient->id,
        'status' => Friendship::STATUS_PENDING,
        'created_at' => Carbon::now()->subDays(3),
        'updated_at' => Carbon::now()->subDays(3),
    ]);

    Friendship::query()->create([
        'sender_id' => $pendingSender->id,
        'recipient_id' => $member->id,
        'status' => Friendship::STATUS_PENDING,
        'created_at' => Carbon::now()->subDays(4),
        'updated_at' => Carbon::now()->subDays(4),
    ]);

    // Blocked relationship to verify the summary tallies gated connections.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $blockedUser->id,
        'status' => Friendship::STATUS_BLOCKED,
        'created_at' => Carbon::now()->subDays(60),
        'updated_at' => Carbon::now()->subDays(60),
    ]);

    // Cross connections between friends to generate non-zero mutual insights.
    Friendship::query()->create([
        'sender_id' => $friendOne->id,
        'recipient_id' => $friendTwo->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(15),
        'created_at' => Carbon::now()->subDays(18),
        'updated_at' => Carbon::now()->subDays(15),
    ]);

    Friendship::query()->create([
        'sender_id' => $friendOne->id,
        'recipient_id' => $friendThree->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(12),
        'created_at' => Carbon::now()->subDays(17),
        'updated_at' => Carbon::now()->subDays(12),
    ]);

    Friendship::query()->create([
        'sender_id' => $friendTwo->id,
        'recipient_id' => $friendThree->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(9),
        'created_at' => Carbon::now()->subDays(14),
        'updated_at' => Carbon::now()->subDays(9),
    ]);

    // Mount the Livewire component and confirm the calculated datasets match the
    // expected totals for the constructed relationship graph.
    $component = Livewire::test(Analytics::class, [
        'entityType' => 'user',
        'entityId' => $member->id,
    ]);

    $component->assertSet('summary.total_friends', 3);
    $component->assertSet('summary.new_friends_last_30_days', 2);
    $component->assertSet('summary.pending_sent', 1);
    $component->assertSet('summary.pending_received', 1);
    $component->assertSet('summary.blocked', 1);
    $component->assertSet('summary.average_acceptance_hours', 240.0);

    // Ensure the category breakdown respects custom categories and the unknown
    // label for uncategorised friendships.
    $categoryBreakdown = $component->get('categoryBreakdown');
    expect($categoryBreakdown)
        ->toBeArray()
        ->and($categoryBreakdown['Family'])->toBe(1)
        ->and($categoryBreakdown['Gym Buddies'])->toBe(1)
        ->and($categoryBreakdown[__('friends.unknown')])->toBe(1);

    // Trend data should report activity for the months where friendships were
    // accepted or created when acceptance timestamps were not recorded.
    $component->assertSet('trendData.2024-12', 1);
    $component->assertSet('trendData.2025-02', 2);

    $mutualInsights = $component->get('mutualInsights');
    expect($mutualInsights)
        ->toBeArray()
        ->and(count($mutualInsights))->toBe(3)
        ->and(collect($mutualInsights)->pluck('name')->all())
            ->toContain($friendOne->name, $friendTwo->name, $friendThree->name);

    // Reset the clock so other tests relying on real time are unaffected.
    Carbon::setTestNow();
});

it('refreshes analytics when the timeframe selection changes', function () {
    // Use a smaller dataset to focus on the reactive trend behaviour.
    Carbon::setTestNow(Carbon::parse('2025-03-01 12:00:00'));
    Cache::flush();

    $member = User::factory()->create();
    $recentFriend = User::factory()->create();

    actingAs($member);

    // Single accepted friendship ensures each dataset has a deterministic count
    // no matter the selected timeframe.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $recentFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(5),
        'created_at' => Carbon::now()->subDays(6),
        'updated_at' => Carbon::now()->subDays(5),
    ]);

    $component = Livewire::test(Analytics::class, [
        'entityType' => 'user',
        'entityId' => $member->id,
    ]);

    // Switch to the three-month range and ensure the dataset condenses to
    // exactly three periods with the recent friendship counted once.
    $component->set('trendRange', '3_months');
    $component->call('loadAnalytics');

    $trendData = $component->get('trendData');
    expect($trendData)
        ->toBeArray()
        ->and(count($trendData))->toBe(3)
        ->and(array_sum($trendData))->toBe(1);

    // Expanding to the yearly range should produce twelve buckets while
    // preserving the same overall total of accepted friendships.
    $component->set('trendRange', '12_months');
    $component->call('loadAnalytics');

    $yearTrend = $component->get('trendData');
    expect($yearTrend)
        ->toBeArray()
        ->and(count($yearTrend))->toBe(12)
        ->and(array_sum($yearTrend))->toBe(1);

    Carbon::setTestNow();
});

it('renders the expected analytics blade view when instantiated manually', function () {
    // Flush cached friendship lookups so the component boot sequence pulls
    // fresh data from the database for the isolated rendering assertion.
    Cache::flush();

    // Create the member context and authenticate so authorization checks mirror
    // the behaviour triggered when Livewire boots in the browser.
    $member = User::factory()->create();
    actingAs($member);

    // Instantiate the component through the container, mount it, and inspect the
    // resulting view to ensure the Blade template matches the render contract.
    $component = app(Analytics::class, [
        'entityType' => 'user',
        'entityId' => $member->id,
    ]);

    $component->mount('user', $member->id);

    $view = $component->render();

    expect($view->name())->toBe('livewire.common.friend.analytics')
        ->and(view()->exists($view->name()))->toBeTrue();
});
