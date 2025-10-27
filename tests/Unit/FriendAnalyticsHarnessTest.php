<?php

use App\Http\Livewire\Common\Friend\Analytics;
use App\Models\Friendship;
use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

// Helper harness to surface protected analytics helpers for direct unit tests.
class FriendAnalyticsHarness extends Analytics
{
    /**
     * Expose the entity bootstrapping helper from the trait so tests can set
     * the context without going through the Livewire lifecycle.
     */
    public function bootstrap(string $entityType, int $entityId): void
    {
        $this->initializeEntity($entityType, $entityId);
    }

    /**
     * Retrieve summary statistics using the component internals.
     */
    public function exposeSummary(): array
    {
        return $this->buildSummaryStatistics($this->getAcceptedFriendshipsQuery());
    }

    /**
     * Retrieve trend data for assertions.
     */
    public function exposeTrend(): array
    {
        return $this->buildTrendData($this->getAcceptedFriendshipsQuery());
    }

    /**
     * Surface the mutual insights array for verification.
     */
    public function exposeMutual(): array
    {
        return $this->buildMutualInsights();
    }

    /**
     * Allow tests to toggle the trend range prior to computing data.
     */
    public function useTrendRange(string $range): void
    {
        $this->trendRange = $range;
    }
}

it('derives accurate summary statistics for user friendships', function () {
    // Freeze time so duration calculations align with deterministic expectations.
    Carbon::setTestNow(Carbon::parse('2025-04-01 09:00:00'));
    Cache::flush();

    // Prepare the subject and related accounts representing each friendship state.
    $member = User::factory()->create();
    $acceptedFriend = User::factory()->create();
    $pendingFriend = User::factory()->create();
    $incomingFriend = User::factory()->create();
    $blockedFriend = User::factory()->create();

    actingAs($member);

    // Accepted friendship with an explicit acceptance timestamp for averaging.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $acceptedFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(5),
        'created_at' => Carbon::now()->subDays(10),
        'updated_at' => Carbon::now()->subDays(5),
    ]);

    // Outgoing pending friendship to exercise the sent counter.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $pendingFriend->id,
        'status' => Friendship::STATUS_PENDING,
        'created_at' => Carbon::now()->subDays(2),
        'updated_at' => Carbon::now()->subDays(2),
    ]);

    // Incoming pending friendship to increment the received counter.
    Friendship::query()->create([
        'sender_id' => $incomingFriend->id,
        'recipient_id' => $member->id,
        'status' => Friendship::STATUS_PENDING,
        'created_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now()->subDay(),
    ]);

    // Blocked relationship to confirm blocked totals.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $blockedFriend->id,
        'status' => Friendship::STATUS_BLOCKED,
        'created_at' => Carbon::now()->subDays(20),
        'updated_at' => Carbon::now()->subDays(20),
    ]);

    $harness = new FriendAnalyticsHarness();
    $harness->bootstrap('user', $member->id);

    $summary = $harness->exposeSummary();

    expect($summary['total_friends'])->toBe(1)
        ->and($summary['new_friends_last_30_days'])->toBe(1)
        ->and($summary['pending_sent'])->toBe(1)
        ->and($summary['pending_received'])->toBe(1)
        ->and($summary['blocked'])->toBe(1)
        ->and($summary['average_acceptance_hours'])->toBe(120.0);

    Carbon::setTestNow();
});

it('builds mutual insights and trends for pet friendships', function () {
    // Ensure cached friend lookups do not bleed between entity types.
    Carbon::setTestNow(Carbon::parse('2025-04-01 09:00:00'));
    Cache::flush();

    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    $friendPet = Pet::factory()->create();
    $mutualPet = Pet::factory()->create();

    actingAs($owner);

    // Direct friendships for the subject pet.
    PetFriendship::query()->create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $friendPet->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(3),
        'created_at' => Carbon::now()->subDays(6),
        'updated_at' => Carbon::now()->subDays(3),
    ]);

    PetFriendship::query()->create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $mutualPet->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(1),
        'created_at' => Carbon::now()->subDays(2),
        'updated_at' => Carbon::now()->subDay(),
    ]);

    // Secondary friendship between the companion pets to create mutual counts.
    PetFriendship::query()->create([
        'pet_id' => $friendPet->id,
        'friend_pet_id' => $mutualPet->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDays(2),
        'created_at' => Carbon::now()->subDays(4),
        'updated_at' => Carbon::now()->subDays(2),
    ]);

    $harness = new FriendAnalyticsHarness();
    $harness->bootstrap('pet', $pet->id);
    $harness->useTrendRange('3_months');

    $trend = $harness->exposeTrend();
    $mutual = $harness->exposeMutual();

    expect($trend)->toBeArray()
        ->and(count($trend))->toBe(3)
        ->and(array_sum($trend))->toBe(2);

    expect($mutual)->toBeArray()
        ->and($mutual)->not->toBeEmpty()
        ->and($mutual[0]['mutual_count'])->toBeGreaterThan(0);

    Carbon::setTestNow();
});
