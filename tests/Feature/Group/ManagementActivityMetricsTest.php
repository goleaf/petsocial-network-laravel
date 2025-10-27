<?php

use App\Http\Livewire\Group\Management\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage for the management dashboard activity analytics.
 */
it('summarises group engagement metrics for moderators', function (): void {
    // Freeze time so the rolling windows for recent activity remain deterministic.
    Carbon::setTestNow('2025-05-10 10:00:00');

    // Authenticate a moderator to exercise the management dashboard.
    $moderator = User::factory()->create();
    actingAs($moderator);

    // Create a category and two groups to populate the analytics surface.
    $category = Category::query()->create([
        'name' => 'Agility Training',
        'slug' => 'agility-training',
        'is_active' => true,
    ]);

    $firstGroup = Group::query()->create([
        'name' => 'Weekend Sprinters',
        'slug' => 'weekend-sprinters',
        'description' => 'Fast-paced practice sessions for energetic pups.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $moderator->id,
    ]);

    $secondGroup = Group::query()->create([
        'name' => 'Rescue Allies',
        'slug' => 'rescue-allies',
        'description' => 'Community support network for rescue partners.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_CLOSED,
        'creator_id' => $moderator->id,
    ]);

    // Seed active and pending members across the groups including recent joins.
    DB::table('group_members')->insert([
        ['group_id' => $firstGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(2), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $firstGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(3), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $firstGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(14), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $firstGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(21), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $firstGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'pending', 'role' => 'member', 'joined_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $secondGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(5), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $secondGroup->id, 'user_id' => User::factory()->create()->id, 'status' => 'active', 'role' => 'member', 'joined_at' => Carbon::now()->subDays(32), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Populate recent discussion activity for the first group and a smaller pulse for the second.
    $topicOne = DB::table('group_topics')->insertGetId([
        'group_id' => $firstGroup->id,
        'user_id' => $moderator->id,
        'title' => 'Sprint warmups',
        'content' => 'Share your best warmup routine.',
        'created_at' => Carbon::now()->subDays(1),
        'updated_at' => Carbon::now()->subDays(1),
    ]);

    $topicTwo = DB::table('group_topics')->insertGetId([
        'group_id' => $firstGroup->id,
        'user_id' => $moderator->id,
        'title' => 'Hydration tips',
        'content' => 'Keep the crew hydrated!',
        'created_at' => Carbon::now()->subDays(4),
        'updated_at' => Carbon::now()->subDays(4),
    ]);

    DB::table('group_topics')->insert([
        'group_id' => $firstGroup->id,
        'user_id' => $moderator->id,
        'title' => 'Older planning thread',
        'content' => 'Archived planning session.',
        'created_at' => Carbon::now()->subDays(40),
        'updated_at' => Carbon::now()->subDays(40),
    ]);

    DB::table('group_topics')->insert([
        'group_id' => $secondGroup->id,
        'user_id' => $moderator->id,
        'title' => 'Foster schedules',
        'content' => 'Coordinate fosters for the month.',
        'created_at' => Carbon::now()->subDays(2),
        'updated_at' => Carbon::now()->subDays(2),
    ]);

    DB::table('group_topic_replies')->insert([
        ['group_topic_id' => $topicOne, 'user_id' => $moderator->id, 'content' => 'Dynamic stretching works wonders.', 'created_at' => Carbon::now()->subHours(6), 'updated_at' => Carbon::now()->subHours(6)],
        ['group_topic_id' => $topicOne, 'user_id' => User::factory()->create()->id, 'content' => 'We love the ladder drills.', 'created_at' => Carbon::now()->subHours(3), 'updated_at' => Carbon::now()->subHours(3)],
        ['group_topic_id' => $topicTwo, 'user_id' => $moderator->id, 'content' => 'Electrolytes on hot days.', 'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)],
        ['group_topic_id' => $topicTwo, 'user_id' => User::factory()->create()->id, 'content' => 'DIY frozen treats keep pups cool.', 'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)],
        ['group_topic_id' => $topicTwo, 'user_id' => User::factory()->create()->id, 'content' => 'Hydration checklists are essential.', 'created_at' => Carbon::now()->subDay(), 'updated_at' => Carbon::now()->subDay()],
    ]);

    // Schedule upcoming events to feed the participation metrics.
    DB::table('group_events')->insert([
        ['group_id' => $firstGroup->id, 'user_id' => $moderator->id, 'title' => 'Trail dash', 'description' => 'Weekly sprint meetup.', 'start_date' => Carbon::now()->addDays(3), 'end_date' => Carbon::now()->addDays(3)->addHour(), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $firstGroup->id, 'user_id' => $moderator->id, 'title' => 'Recovery picnic', 'description' => 'Cooldown and snacks.', 'start_date' => Carbon::now()->addDays(5), 'end_date' => Carbon::now()->addDays(5)->addHours(2), 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $secondGroup->id, 'user_id' => $moderator->id, 'title' => 'Adoption day', 'description' => 'Weekend adoption fair.', 'start_date' => Carbon::now()->subDays(1), 'end_date' => Carbon::now()->subDay(), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Render the Livewire component and capture the computed analytics payloads.
    $component = Livewire::test(Index::class);

    $summary = $component->get('summaryMetrics');
    expect($summary['total_groups'])->toBe(2)
        ->and($summary['active_members'])->toBe(6)
        ->and($summary['pending_members'])->toBe(1)
        ->and($summary['topics_last_seven_days'])->toBe(3)
        ->and($summary['replies_last_seven_days'])->toBe(5)
        ->and($summary['upcoming_events'])->toBe(2)
        ->and($summary['engagement_rate'])->toBe(1.33);

    $activity = $component->get('groupActivity');
    expect($activity[$firstGroup->id]['active_members'])->toBe(4)
        ->and($activity[$firstGroup->id]['new_members'])->toBe(2)
        ->and($activity[$firstGroup->id]['topics_last_seven_days'])->toBe(2)
        ->and($activity[$firstGroup->id]['replies_last_seven_days'])->toBe(5)
        ->and($activity[$firstGroup->id]['upcoming_events'])->toBe(2)
        ->and($activity[$firstGroup->id]['engagement_rate'])->toBe(1.75);

    expect($activity[$secondGroup->id]['active_members'])->toBe(2)
        ->and($activity[$secondGroup->id]['new_members'])->toBe(1)
        ->and($activity[$secondGroup->id]['topics_last_seven_days'])->toBe(1)
        ->and($activity[$secondGroup->id]['replies_last_seven_days'])->toBe(0)
        ->and($activity[$secondGroup->id]['upcoming_events'])->toBe(0)
        ->and($activity[$secondGroup->id]['engagement_rate'])->toBe(0.5);

    // Confirm the rendered table includes the engagement terminology for accessibility.
    $component->assertSee('Engagement rate');

    // Release the frozen clock to avoid time drift across other tests.
    Carbon::setTestNow();
});
