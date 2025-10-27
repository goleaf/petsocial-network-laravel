<?php

use App\Http\Livewire\Common\Friend\Export as FriendExportComponent;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function Pest\Laravel\actingAs;

require_once __DIR__.'/FriendExportTestHelpers.php';

it('exposes the blade template used by the friend export component', function (): void {
    // The Livewire component relies on this blade file, so the view must remain registered in the view factory.
    expect(view()->exists('livewire.common.friend.export'))->toBeTrue();
});

it('exports selected friends to a csv file with contact data', function (): void {
    [$owner, $friend] = createFriendExportUsers();

    actingAs($owner);

    $component = app(FriendExportComponent::class);
    $component->mount('user', $owner->id);
    $component->includeEmails = true;
    $component->includePhones = true;
    $component->selectedFriends = [$friend->id];
    $component->exportFormat = 'csv';

    /** @var BinaryFileResponse $response */
    $response = $component->export();

    expect($response)->toBeInstanceOf(BinaryFileResponse::class);

    $fileName = 'user_friends_'.date('Y-m-d').'.csv';
    $path = 'exports/'.$fileName;

    // The export should persist to the local disk using the expected naming convention.
    expect(Storage::disk('local')->exists($path))->toBeTrue();

    $contents = Storage::disk('local')->get($path);

    expect($contents)->toContain('Name,Username,Email,Phone');
    expect($contents)->toContain('Jordan Breeze');
    expect($contents)->toContain('jordan-breeze');
    expect($contents)->toContain('jordan@example.com');
    expect($contents)->toContain('555-0101');

    // Clean up the generated file now that the assertions have executed.
    Storage::disk('local')->delete($path);
});

it('returns an error flash when no matching users exist for the selected IDs', function (): void {
    [$owner, $friend] = createFriendExportUsers();

    actingAs($owner);

    $component = app(FriendExportComponent::class);
    $component->mount('user', $owner->id);
    $component->selectedFriends = [$friend->id + 50];

    $component->export();

    expect(session('error'))->toBe(__('friends.no_data_to_export'));
});
