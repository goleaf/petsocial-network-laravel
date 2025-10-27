<?php

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

$sqlitePath = __DIR__.'/../../database/testing.sqlite';

if (! file_exists($sqlitePath)) {
    touch($sqlitePath);
}

uses(RefreshDatabase::class);

it('respects channel preferences when delivering notifications', function (): void {
    // Fake notifications so queued channels do not trigger external services.
    Notification::fake();

    $user = User::factory()->create();
    $user->notification_preferences = [
        'channels' => [
            'in_app' => true,
            'email' => false,
            'push' => false,
        ],
        'categories' => [
            'system' => [
                'enabled' => true,
                'priority' => 'normal',
                'frequency' => 'instant',
            ],
        ],
        'frequency' => [
            'normal' => 'instant',
        ],
        'digest' => [
            'enabled' => false,
            'interval' => 'daily',
            'send_time' => '08:00',
        ],
    ];

    $service = app(NotificationService::class);
    $notification = $service->send($user, 'Test message', [
        'category' => 'system',
        'priority' => 'normal',
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    expect($notification->channels)->toBe(['in_app'])
        ->and($notification->delivered_via)->toBeNull()
        ->and(UserNotification::count())->toBe(1);
});

it('batches notifications sharing a key', function (): void {
    // Fake notifications so queued channels do not trigger external services.
    Notification::fake();

    $user = User::factory()->create();
    $service = app(NotificationService::class);

    $service->send($user, 'First update', [
        'category' => 'system',
        'batch_key' => 'activity:1',
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    $updated = $service->send($user, 'Second update', [
        'category' => 'system',
        'batch_key' => 'activity:1',
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    expect(UserNotification::count())->toBe(1)
        ->and(Str::contains($updated->message, 'Second update'))->toBeTrue()
        ->and($updated->data['aggregate_count'])->toBe(2);
});

it('creates digest notifications through the scheduled command', function (): void {
    Config::set('notifications.digest.default_time', now()->subMinutes(5)->format('H:i'));

    $user = User::factory()->create();

    UserNotification::factory()->create([
        'user_id' => $user->id,
        'category' => 'system',
        'priority' => 'normal',
        'message' => 'Pending update',
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    artisan('notifications:send-digests')->assertExitCode(0);

    $digest = UserNotification::where('user_id', $user->id)
        ->where('is_digest', true)
        ->latest()
        ->first();

    expect($digest)->not->toBeNull()
        ->and($digest->data['items'])->toHaveCount(1);
});

it('delivers queued channels when preferences allow external delivery', function (): void {
    // Ensure notifications can be asserted without touching external mail or broadcast drivers.
    Notification::fake();

    $user = User::factory()->create(['email' => 'jane@example.test']);
    $service = app(NotificationService::class);

    $service->send($user, 'Security alert', [
        'category' => 'system',
        'priority' => 'high',
        'channels' => ['in_app', 'email', 'push'],
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    Notification::assertSentTo($user, SystemNotification::class, function (SystemNotification $notification, array $channels) {
        // Only non in-app channels should be forwarded to Laravel's notification layer.
        expect(array_values($channels))->toBe(['mail', 'broadcast']);

        return true;
    });
});

it('avoids batching when an existing notification falls outside the window', function (): void {
    // Fake notifications so queued channels do not trigger external services.
    Notification::fake();

    $user = User::factory()->create();
    $service = app(NotificationService::class);

    $existing = UserNotification::factory()->create([
        'user_id' => $user->id,
        'category' => 'system',
        'priority' => 'normal',
        'message' => 'Old update',
        'batch_key' => 'activity:1',
        'sender_id' => $user->id,
        'sender_type' => User::class,
        'created_at' => now()->subMinutes(20),
        'updated_at' => now()->subMinutes(20),
    ]);

    $service->send($user, 'Fresh update', [
        'category' => 'system',
        'batch_key' => 'activity:1',
        'sender_id' => $user->id,
        'sender_type' => User::class,
    ]);

    expect(UserNotification::where('batch_key', 'activity:1')->count())->toBe(2)
        ->and($existing->fresh()->message)->toBe('Old update');
});

it('queues category notifications for digest delivery when frequency requires batching', function (): void {
    // Instantiate the service to evaluate the digest decision helper.
    $service = app(NotificationService::class);

    $user = User::factory()->create([
        'notification_preferences' => [
            'channels' => [
                'in_app' => true,
                'email' => true,
                'push' => true,
            ],
            'categories' => [
                'system' => [
                    'enabled' => true,
                    'priority' => 'normal',
                    'frequency' => 'daily',
                ],
            ],
            'frequency' => [
                'normal' => 'instant',
            ],
            'digest' => [
                'enabled' => true,
                'interval' => 'daily',
                'send_time' => '08:00',
            ],
        ],
    ]);

    expect($service->shouldQueueForDigest($user, 'system', 'normal'))->toBeTrue();
});

it('sanitises invalid preference updates before saving', function (): void {
    // Instantiate the service to exercise the preference hygiene helper.
    $service = app(NotificationService::class);

    $user = User::factory()->create();

    $clean = $service->cleanPreferences($user, [
        'channels' => [
            'email' => false,
            'push' => 'yes',
        ],
        'categories' => [
            'system' => [
                'enabled' => '0',
                'priority' => 'invalid',
                'frequency' => 'invalid',
            ],
        ],
        'frequency' => [
            'normal' => 'never',
        ],
        'digest' => [
            'enabled' => true,
            'interval' => 'monthly',
            'send_time' => '25:99',
        ],
    ]);

    expect($clean['channels']['email'])->toBeFalse()
        ->and($clean['channels']['push'])->toBeTrue()
        ->and($clean['categories']['system']['enabled'])->toBeFalse()
        ->and($clean['categories']['system']['priority'])->toBe('high')
        ->and($clean['categories']['system']['frequency'])->toBe('instant')
        ->and($clean['frequency']['normal'])->toBe('hourly')
        ->and($clean['digest']['interval'])->toBe('daily')
        ->and($clean['digest']['send_time'])->toBe('08:00');
});
