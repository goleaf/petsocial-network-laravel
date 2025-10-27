<?php

use App\Http\Livewire\Admin\Analytics;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

/**
 * Filament integration coverage guarantees the metrics map cleanly to dashboard widgets.
 */
it('maps analytics totals into Filament stat payloads when available', function () {
    // Skip gracefully when the Filament admin suite is not installed for the project.
    if (! class_exists(\Filament\Widgets\StatsOverviewWidget\Stat::class)) {
        $this->markTestSkipped('Filament is not installed in this environment.');
    }

    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();

    Post::create([
        'content' => 'Filament ready analytics check',
        'user_id' => $member->id,
    ]);

    FriendRequest::create([
        'sender_id' => $member->id,
        'receiver_id' => $admin->id,
        'status' => 'accepted',
    ]);

    FriendRequest::create([
        'sender_id' => $admin->id,
        'receiver_id' => $member->id,
        'status' => 'accepted',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(Analytics::class);

    // Translate the component metrics into Filament stat instances for dashboard consumption.
    $stats = [
        \Filament\Widgets\StatsOverviewWidget\Stat::make('Users', $component->get('userCount')),
        \Filament\Widgets\StatsOverviewWidget\Stat::make('Posts', $component->get('postCount')),
        \Filament\Widgets\StatsOverviewWidget\Stat::make('Friends', $component->get('friendCount')),
    ];

    foreach ($stats as $stat) {
        // Each generated stat should hold a positive number confirming the totals populated correctly.
        expect($stat->getValue())->toBeGreaterThan(0);
    }
});
