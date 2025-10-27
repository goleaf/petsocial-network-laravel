<?php

use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Feature tests covering pet friendship data exports across supported formats.
 */
beforeEach(function (): void {
    Storage::fake('public');
    Carbon::setTestNow(now());
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('exports accepted pet friendships to CSV with owner contact metadata', function (): void {
    $context = setupPetFriendExportData();

    /** @var Pet $pet */
    $pet = $context['pet'];

    $url = $pet->exportFriendsToCSV();
    $path = 'exports/'.basename($url);

    Storage::disk('public')->assertExists($path);

    $contents = Storage::disk('public')->get($path);

    expect($contents)->toContain('Name,Type,Breed,Category,Since,Owner,"Owner Email","Owner Phone"');
    expect($contents)->toContain($context['friend_name']);
    expect($contents)->toContain($context['friend_type']);
    expect($contents)->toContain($context['friend_breed']);
    expect($contents)->toContain($context['friend_category']);
    expect($contents)->toContain($context['since']);
    expect($contents)->toContain($context['owner_name']);
    expect($contents)->toContain($context['owner_email']);
});

it('exports accepted pet friendships to JSON and VCF formats', function (): void {
    $context = setupPetFriendExportData();

    /** @var Pet $pet */
    $pet = $context['pet'];

    $jsonUrl = $pet->exportFriendsToJson();
    $jsonPath = 'exports/'.basename($jsonUrl);

    Storage::disk('public')->assertExists($jsonPath);

    $payload = json_decode(Storage::disk('public')->get($jsonPath), true);

    expect($payload)->toBeArray();
    expect($payload)->toHaveCount(1);
    expect($payload[0])->toMatchArray([
        'name' => $context['friend_name'],
        'type' => $context['friend_type'],
        'breed' => $context['friend_breed'],
        'category' => $context['friend_category'],
        'since' => $context['since'],
        'owner' => [
            'name' => $context['owner_name'],
            'email' => $context['owner_email'],
            'phone' => null,
        ],
    ]);

    $vcfUrl = $pet->exportFriendsToVcf();
    $vcfPath = 'exports/'.basename($vcfUrl);

    Storage::disk('public')->assertExists($vcfPath);

    $vcf = Storage::disk('public')->get($vcfPath);

    expect($vcf)->toContain('BEGIN:VCARD');
    expect($vcf)->toContain('FN:'.$context['owner_name']);
    expect($vcf)->toContain('NICKNAME:'.$context['friend_name']);
    expect($vcf)->toContain('NOTE:Pet Type: '.$context['friend_type'].'; Breed: '.$context['friend_breed'].'; Category: '.$context['friend_category'].'; Friends Since: '.$context['since']);
});

/**
 * Seed a deterministic pet friendship used by the export feature tests.
 */
function setupPetFriendExportData(): array
{
    $owner = User::factory()->create([
        'name' => 'Jamie Rivera',
        'email' => 'jamie@example.com',
    ]);

    $pet = Pet::factory()->for($owner)->create([
        'name' => 'Luna',
        'type' => 'dog',
        'breed' => 'Husky',
    ]);

    $friendOwner = User::factory()->create([
        'name' => 'Casey Owner',
        'email' => 'casey@example.com',
    ]);

    $friendPet = Pet::factory()->for($friendOwner)->create([
        'name' => 'Nova',
        'type' => 'cat',
        'breed' => 'Bengal',
    ]);

    PetFriendship::create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $friendPet->id,
        'category' => 'Playmates',
        'status' => PetFriendship::STATUS_ACCEPTED,
    ]);

    return [
        'pet' => $pet->fresh(),
        'friend_name' => $friendPet->name,
        'friend_type' => $friendPet->type,
        'friend_breed' => $friendPet->breed,
        'friend_category' => 'Playmates',
        'owner_name' => $friendOwner->name,
        'owner_email' => $friendOwner->email,
        'since' => now()->toDateString(),
    ];
}
