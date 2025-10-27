# Notification System Overview

The notification platform delivers activity updates across multiple channels while respecting member preferences. It expands the in-app notification center with batching, filtering, and digest delivery.

## Delivery Channels
- **In-app feed** – all notifications are persisted to `user_notifications` and surface in the Livewire notification center.
- **Email** – high-urgency and digest notifications trigger the `SystemNotification` mailable when members keep email delivery enabled.
- **Push/broadcast** – real-time events emit broadcast payloads for connected clients. Push delivery follows the `push` channel preference toggle.

## Priority & Categories
- Priorities (`low`, `normal`, `high`, `critical`) influence default channels and member-configured frequencies.
- Categories describe the origin of a notification (`messages`, `friend_requests`, `engagement`, `reminders`, `system`, `digest`).
- The notification center exposes priority and category filters and tags each row so members can focus on specific workflows.

## Preferences
- Member preferences live in the `notification_preferences` JSON column on `users` and are normalised by `App\Services\NotificationService`.
- The **Settings → Notification preferences** panel lets members:
  - Enable/disable channels (`in_app`, `email`, `push`).
  - Choose default priority frequency per urgency level.
  - Override priority/frequency per category.
- Configure digest cadence (daily or weekly) and send time.
- Legacy boolean preferences are migrated automatically the next time preferences are loaded.
- Digest send times are validated against 24-hour boundaries; invalid entries fall back to the configured default window.

## Batching
- Notifications sharing a `batch_key` within the configured window (10 minutes by default) merge into a single record.
- Batched messages track the most recent message plus an aggregate count to avoid spamming members.
- Translation key `notifications.batch_summary` controls the summary sentence that appears when batching occurs.

## Scheduled Digests
- `php artisan notifications:send-digests` compiles unread, non-digest notifications according to preference windows.
- The command runs hourly (see `app/Console/Kernel.php`) and checks whether the preferred send time and interval have elapsed since the last digest.
- Each digest is stored with `is_digest = true` and includes the latest 20 unread items for quick review.

## Extending
- Update `config/notifications.php` to add new categories, adjust default channel mixes, or change batching intervals.
- When adding new notification sources, use `App\Services\NotificationService::send()` so channel logic and preferences remain centralised.
- Tests covering notifications live under `tests/Feature/NotificationServiceTest.php` and now exercise channel delivery, batching windows, digest scheduling, and preference hygiene. The suite automatically loads the stub environment in `tests/environment/.env.testing` whenever a project-level `.env` file is absent, so keep that stub in sync with new configuration keys.
- Share button interactions are also covered by `tests/Feature/Content/ShareButtonFeatureTest.php`, which verifies share notifications continue to target post authors correctly.
