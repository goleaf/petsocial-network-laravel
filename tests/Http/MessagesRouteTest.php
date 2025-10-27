<?php

use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Friendship;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests away from the messages page', function (): void {
    // Guests should be redirected to login to protect private conversations.
    get(route('messages'))->assertRedirect(route('login'));
});

it('allows authenticated users to load the messages page', function (): void {
    // Prepare the transient SQLite schema so the users table exists for authentication.
    prepareTestDatabase();

    // Authenticate a user to ensure the messages route becomes accessible.
    $user = User::factory()->create();
    $user->setRelation('friends', collect());
    actingAs($user);

    // Confirm the protected page renders successfully for authorised members.
    get(route('messages'))->assertOk();
});

it('renders the livewire messages component blade inside the layout', function (): void {
    // Prepare the schema to allow guard interactions against the users table.
    prepareTestDatabase();

    // Authenticate a user with an empty friend list to keep the view rendering deterministic.
    $user = User::factory()->create();

    // Create a friend and the corresponding accepted friendship so the list renders a conversation entry.
    $friend = User::factory()->create();
    Friendship::create([
        'sender_id' => $user->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Hydrate the relation to mimic the eager-loaded friends collection used on mount.
    $user->setRelation('friends', collect([$friend]));
    actingAs($user);

    // Fetch the page and ensure the Livewire component and its Blade markers are present.
    get(route('messages'))
        ->assertSeeLivewire(MessagesComponent::class)
        ->assertSee('wire:click="selectConversation(', false);
});
