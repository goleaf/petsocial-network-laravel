<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\SystemNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * Coordinate multi-channel notifications with user preferences, batching, and digests.
 */
class NotificationService
{
    /**
     * Dispatch a notification to the provided user while respecting preferences.
     */
    public function send(User $recipient, string $message, array $payload = []): UserNotification
    {
        $payload = $this->normalisePayload($recipient, $message, $payload);

        [$notification, $isNew] = $this->storeNotification($recipient, $payload);

        if ($isNew) {
            $this->deliverChannels($recipient, $notification, $payload);
        }

        return $notification;
    }

    /**
     * Determine if a digest should be created rather than an instant message.
     */
    public function shouldQueueForDigest(User $recipient, string $category, string $priority): bool
    {
        $preferences = $this->preferences($recipient);
        $categoryPrefs = $preferences['categories'][$category] ?? [];

        return ($preferences['digest']['enabled'] ?? false)
            && (($categoryPrefs['frequency'] ?? null) === 'daily' || ($preferences['frequency'][$priority] ?? null) === 'daily');
    }

    /**
     * Persist notification metadata to the application table, applying batching when required.
     */
    protected function storeNotification(User $recipient, array $payload): array
    {
        if ($payload['batch_key']) {
            $existing = $this->findBatchCandidate($recipient, $payload['batch_key']);

            if ($existing instanceof UserNotification) {
                $existing->incrementAggregate($payload['message']);

                return [$existing->fresh(), false];
            }
        }

        $notification = $recipient->userNotifications()->create([
            'sender_id' => $payload['sender_id'],
            'sender_type' => $payload['sender_type'],
            'type' => $payload['type'],
            'category' => $payload['category'],
            'priority' => $payload['priority'],
            'message' => $payload['message'],
            'data' => $payload['data'],
            'channels' => $payload['channels'],
            'batch_key' => $payload['batch_key'],
            'scheduled_for' => $payload['scheduled_for'],
            'is_digest' => $payload['is_digest'],
            'action_text' => $payload['action_text'],
            'action_url' => $payload['action_url'],
        ]);

        return [$notification, true];
    }

    /**
     * Deliver the notification over the configured channels.
     */
    protected function deliverChannels(User $recipient, UserNotification $notification, array $payload): void
    {
        $channels = $payload['channels'];

        $laravelChannels = array_filter(array_map(function (string $channel) use ($recipient): ?string {
            if ($channel === 'in_app') {
                return null;
            }

            $map = Config::get('notifications.channel_map');

            if (! Arr::exists($map, $channel)) {
                return null;
            }

            if ($channel === 'email' && empty($recipient->email)) {
                return null;
            }

            return $map[$channel];
        }, $channels));

        if (! empty($laravelChannels)) {
            $recipient->notify(new SystemNotification(
                $notification,
                $payload['subject'],
                $payload['action_text'],
                $payload['action_url'],
                $laravelChannels
            ));
        }

        $delivered = collect($channels)
            ->reject(fn (string $channel) => $channel === 'in_app')
            ->values()
            ->all();

        if (! empty($delivered)) {
            $notification->markDelivered($delivered);
        }
    }

    /**
     * Resolve preferences, default metadata, and channel selections.
     */
    protected function normalisePayload(User $recipient, string $message, array $payload): array
    {
        $preferences = $this->preferences($recipient);
        $category = $payload['category'] ?? 'system';
        $priority = $payload['priority'] ?? $this->resolveDefaultPriority($category);
        $channels = $this->resolveChannels($preferences, $category, $priority, $payload['channels'] ?? []);

        return array_merge([
            'type' => $payload['type'] ?? Str::slug($category, '_'),
            'category' => $category,
            'priority' => $priority,
            'message' => $message,
            'data' => $payload['data'] ?? [],
            'channels' => $channels,
            'batch_key' => $payload['batch_key'] ?? null,
            'sender_id' => $payload['sender_id'] ?? null,
            'sender_type' => $payload['sender_type'] ?? null,
            'scheduled_for' => $payload['scheduled_for'] ?? null,
            'is_digest' => $payload['is_digest'] ?? false,
            'action_text' => $payload['action_text'] ?? null,
            'action_url' => $payload['action_url'] ?? null,
            'subject' => $payload['subject'] ?? $this->subjectForCategory($category),
        ], $payload);
    }

    /**
     * Locate an existing notification that should be batched with the new event.
     */
    protected function findBatchCandidate(User $recipient, string $batchKey): ?UserNotification
    {
        $window = Config::get('notifications.batching.window_seconds', 600);

        return $recipient->userNotifications()
            ->where('batch_key', $batchKey)
            ->where('created_at', '>=', now()->subSeconds($window))
            ->whereNull('read_at')
            ->latest('id')
            ->first();
    }

    /**
     * Transform stored preferences into a consistent structure.
     */
    protected function preferences(User $recipient): array
    {
        $defaults = [
            'channels' => [
                'in_app' => true,
                'email' => true,
                'push' => true,
            ],
            'frequency' => [
                'low' => 'daily',
                'normal' => 'hourly',
                'high' => 'instant',
                'critical' => 'instant',
            ],
            'categories' => collect(Config::get('notifications.categories', []))->mapWithKeys(function (array $definition, string $key) {
                return [$key => [
                    'enabled' => true,
                    'priority' => $definition['default_priority'] ?? 'normal',
                    'frequency' => 'instant',
                ]];
            })->toArray(),
            'digest' => [
                'enabled' => true,
                'interval' => Config::get('notifications.digest.default_interval', 'daily'),
                'send_time' => Config::get('notifications.digest.default_time', '08:00'),
                'last_sent_at' => null,
            ],
        ];

        $stored = $recipient->notification_preferences ?? [];

        return array_replace_recursive($defaults, $this->migrateLegacyPreferences($stored));
    }

    /**
     * Expose the resolved preferences for UI consumers.
     */
    public function preferencesFor(User $recipient): array
    {
        return $this->preferences($recipient);
    }

    /**
     * Sanitize incoming preference updates against supported options.
     */
    public function cleanPreferences(User $recipient, array $incoming): array
    {
        $defaults = $this->preferences($recipient);
        $clean = $defaults;

        foreach ($defaults['channels'] as $channel => $enabled) {
            $clean['channels'][$channel] = (bool) Arr::get($incoming, "channels.{$channel}", $enabled);
        }

        $availablePriorities = Config::get('notifications.priorities', []);
        $availableFrequencies = array_keys(Config::get('notifications.frequencies', []));

        foreach ($defaults['categories'] as $category => $config) {
            $clean['categories'][$category]['enabled'] = (bool) Arr::get($incoming, "categories.{$category}.enabled", $config['enabled']);

            $priority = Arr::get($incoming, "categories.{$category}.priority", $config['priority']);
            if (in_array($priority, $availablePriorities, true)) {
                $clean['categories'][$category]['priority'] = $priority;
            }

            $frequency = Arr::get($incoming, "categories.{$category}.frequency", $config['frequency']);
            if (in_array($frequency, $availableFrequencies, true)) {
                $clean['categories'][$category]['frequency'] = $frequency;
            }
        }

        foreach ($defaults['frequency'] as $priority => $setting) {
            $frequency = Arr::get($incoming, "frequency.{$priority}", $setting);

            if (in_array($frequency, $availableFrequencies, true)) {
                $clean['frequency'][$priority] = $frequency;
            }
        }

        $clean['digest']['enabled'] = (bool) Arr::get($incoming, 'digest.enabled', $defaults['digest']['enabled']);

        $interval = Arr::get($incoming, 'digest.interval', $defaults['digest']['interval']);
        if (in_array($interval, Config::get('notifications.digest.intervals', []), true)) {
            $clean['digest']['interval'] = $interval;
        }

        $sendTime = Arr::get($incoming, 'digest.send_time', $defaults['digest']['send_time']);
        $clean['digest']['send_time'] = $this->isValidTimeString((string) $sendTime)
            ? $sendTime
            : $defaults['digest']['send_time'];

        $clean['digest']['last_sent_at'] = $defaults['digest']['last_sent_at'] ?? null;

        return $clean;
    }

    /**
     * Convert legacy boolean preference lists into the structured format.
     */
    protected function migrateLegacyPreferences(array $stored): array
    {
        if (isset($stored['channels']) || isset($stored['categories'])) {
            return $stored;
        }

        $categories = [];

        foreach ($stored as $key => $value) {
            if (! is_bool($value)) {
                continue;
            }

            $categories[$key] = [
                'enabled' => $value,
                'priority' => Config::get("notifications.categories.{$key}.default_priority", 'normal'),
                'frequency' => 'instant',
            ];
        }

        return [
            'channels' => [
                'in_app' => true,
                'email' => $stored['email_notifications'] ?? true,
                'push' => $stored['push_notifications'] ?? true,
            ],
            'categories' => $categories,
        ];
    }

    /**
     * Resolve the default priority for a category.
     */
    protected function resolveDefaultPriority(string $category): string
    {
        return Config::get("notifications.categories.{$category}.default_priority", Config::get('notifications.priorities.1', 'normal'));
    }

    /**
     * Build the list of channels that should deliver a notification.
     */
    protected function resolveChannels(array $preferences, string $category, string $priority, array $requested): array
    {
        $channels = ! empty($requested)
            ? $requested
            : Config::get("notifications.default_channels.{$priority}", ['in_app']);

        $channels = collect($channels)
            ->filter(function (string $channel) use ($preferences) {
                return Arr::get($preferences, "channels.{$channel}", true) === true;
            })
            ->values();

        if (Arr::get($preferences, "categories.{$category}.enabled", true) === false) {
            $channels = $channels->reject(fn (string $channel) => $channel !== 'in_app');
        }

        return $channels->unique()->values()->all();
    }

    /**
     * Compute a default subject that is aligned with the category label.
     */
    protected function subjectForCategory(string $category): string
    {
        $label = Config::get("notifications.categories.{$category}.label");

        return $label ? __('notifications.subject_generic', ['category' => $label]) : __('notifications.subject_default');
    }

    /**
     * Validate that a provided time uses HH:MM format within 24-hour boundaries.
     */
    protected function isValidTimeString(string $candidate): bool
    {
        if (preg_match('/^\d{2}:\d{2}$/', $candidate) !== 1) {
            return false;
        }

        [$hour, $minute] = array_map('intval', explode(':', $candidate));

        return $hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60;
    }

    /**
     * Update the stored digest timestamp for a user.
     */
    public function recordDigestSent(User $user): void
    {
        $preferences = $this->preferences($user);
        $preferences['digest']['last_sent_at'] = now()->toDateTimeString();

        $user->forceFill(['notification_preferences' => $preferences])->save();
    }
}
