<?php

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

// Refresh the in-memory SQLite database so the notification tables mirror production schema.
uses(RefreshDatabase::class);

it('renders the notification center page for the authenticated user', function (): void {
    // Clear any cached notification counts to ensure the component recomputes during the request cycle.
    Cache::flush();

    // Create a user with a pending notification so the Livewire view has meaningful content.
    $user = User::factory()->create();
    UserNotification::factory()
        ->for($user)
        ->create([
            'message' => 'Daily digest ready',
            'category' => 'system',
            'priority' => 'high',
        ]);

    // Visit the notifications route as the owner to confirm the page renders and exposes the message.
    $response = $this->actingAs($user)->get(route('notifications'));

    $response->assertOk();
    $response->assertSee('Daily digest ready');
    $response->assertSee(__('notifications.your_notifications'));
});
