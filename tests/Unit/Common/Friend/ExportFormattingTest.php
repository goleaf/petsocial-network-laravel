<?php

use App\Http\Livewire\Common\Friend\Export;

it('generates csv exports with optional contact columns', function (): void {
    // Build a minimal component instance with contact toggles enabled.
    $component = new Export();
    $component->includeEmails = true;
    $component->includePhones = true;

    $users = collect([
        (object) [
            'name' => 'Jordan Breeze',
            'username' => 'jordan-breeze',
            'email' => 'jordan@example.com',
            'phone' => '555-0101',
        ],
    ]);

    $csv = invokeFriendExportFormatter($component, 'generateCsv', [$users]);

    expect($csv)->toContain('Name,Username,Email,Phone');
    expect($csv)->toContain('"Jordan Breeze"');
    expect($csv)->toContain('"jordan-breeze"');
    expect($csv)->toContain('"jordan@example.com"');
    expect($csv)->toContain('"555-0101"');
});

it('produces json exports honouring enabled fields', function (): void {
    $component = new Export();
    $component->includeEmails = true;
    $component->includePhones = false;

    $users = collect([
        (object) [
            'name' => 'Taylor Horizon',
            'username' => 'taylor-horizon',
            'email' => 'taylor@example.com',
            'phone' => '555-0100',
        ],
    ]);

    $json = invokeFriendExportFormatter($component, 'generateJson', [$users]);
    $payload = json_decode($json, true);

    expect($payload)->toMatchArray([
        [
            'name' => 'Taylor Horizon',
            'username' => 'taylor-horizon',
            'email' => 'taylor@example.com',
        ],
    ]);
});

it('renders vcf cards with conditional email and phone data', function (): void {
    $component = new Export();
    $component->includeEmails = true;
    $component->includePhones = true;

    $users = collect([
        (object) [
            'name' => 'Morgan Follower',
            'username' => 'morgan-follower',
            'email' => 'morgan@example.com',
            'phone' => '555-0102',
        ],
    ]);

    $vcf = invokeFriendExportFormatter($component, 'generateVcf', [$users]);

    expect($vcf)->toContain('BEGIN:VCARD');
    expect($vcf)->toContain('FN:Morgan Follower');
    expect($vcf)->toContain('NICKNAME:morgan-follower');
    expect($vcf)->toContain('EMAIL;TYPE=INTERNET:morgan@example.com');
    expect($vcf)->toContain('TEL;TYPE=CELL:555-0102');
});

/**
 * Access private formatting helpers so they can be validated in isolation.
 */
function invokeFriendExportFormatter(Export $component, string $method, array $arguments): mixed
{
    $reflection = new \ReflectionClass($component);
    $formatter = $reflection->getMethod($method);
    $formatter->setAccessible(true);

    return $formatter->invokeArgs($component, $arguments);
}
