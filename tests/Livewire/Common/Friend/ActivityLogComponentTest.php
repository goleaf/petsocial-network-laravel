<?php

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\Friendship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

/**
 * Livewire interaction tests for the activity log component.
 */
beforeEach(function () {
    // Rebuild the schema for each Livewire run so queries have the necessary tables.
    prepareTestDatabase();
    Cache::flush();
});

it('resets pagination when filters change and returns filtered activity data', function () {
    Carbon::setTestNow(Carbon::parse('2025-06-01 09:00:00'));

    // Seed a user with both self and friend activities to drive the view output.
    $user = User::factory()->create();
    $friend = User::factory()->create();

    Friendship::query()->create([
        'sender_id' => $user->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => Carbon::now()->subDay(),
    ]);

    DB::table('user_activities')->insert([
        [
            'user_id' => $user->id,
            'activity_type' => 'post_created',
            'type' => 'post_created',
            'created_at' => Carbon::now()->subHours(2),
            'updated_at' => Carbon::now()->subHours(2),
        ],
        [
            'user_id' => $user->id,
            'activity_type' => 'profile_update',
            'type' => 'profile_update',
            'created_at' => Carbon::now()->subHours(3),
            'updated_at' => Carbon::now()->subHours(3),
        ],
        [
            'user_id' => $friend->id,
            'activity_type' => 'post_created',
            'type' => 'post_created',
            'created_at' => Carbon::now()->subHours(1),
            'updated_at' => Carbon::now()->subHours(1),
        ],
    ]);

    // Authenticate as the owner so the component passes the privacy checks during mount.
    actingAs($user);

    $component = Livewire::test(ActivityLog::class, [
        'entityType' => 'user',
        'entityId' => $user->id,
    ]);

    // Simulate navigating to a later page before applying a filter.
    $component->set('perPage', 1);
    $component->call('gotoPage', 2);

    $component->set('typeFilter', 'post_created')
        ->assertViewIs('livewire.common.friend.activity-log')
        ->assertViewHas('activities', function ($paginator) {
            // The paginator should contain only the filtered activity entries.
            return $paginator->count() === 1
                && $paginator->first()->activity_type === 'post_created';
        })
        ->assertViewHas('friendActivities', function ($collection) use ($friend) {
            return $collection->pluck('user_id')->contains($friend->id);
        });

    // Confirm pagination resets after the filter mutates to keep the dataset in sync.
    expect($component->instance()->getPage())->toBe(1);

    Carbon::setTestNow();
});
