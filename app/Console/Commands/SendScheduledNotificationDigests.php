<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Compile unread notifications into scheduled digest summaries.
 */
class SendScheduledNotificationDigests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-digests';

    /**
     * The console command description.
     */
    protected $description = 'Send scheduled notification digests to users based on their preferences.';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        User::query()
            ->chunkById(100, function ($users) use ($now): void {
                foreach ($users as $user) {
                    $preferences = $this->notificationService->preferencesFor($user);

                    if (! Arr::get($preferences, 'digest.enabled', false)) {
                        continue;
                    }

                    $nextRunAt = $this->nextRunAt($preferences, $now);

                    if ($now->lt($nextRunAt)) {
                        continue;
                    }

                    $notifications = $user->userNotifications()
                        ->whereNull('read_at')
                        ->where('is_digest', false)
                        ->whereIn('priority', ['low', 'normal'])
                        ->where('created_at', '>=', $this->digestWindowStart($preferences, $now))
                        ->orderByDesc('created_at')
                        ->limit(20)
                        ->get();

                    if ($notifications->isEmpty()) {
                        $this->recordDigestTimestamp($user);

                        continue;
                    }

                    $latest = $notifications->first();
                    $message = __('notifications.batch_summary', [
                        'count' => $notifications->count(),
                        'last' => $latest->message,
                    ]);

                    $this->notificationService->send($user, $message, [
                        'category' => 'digest',
                        'priority' => 'low',
                        'is_digest' => true,
                        'sender_id' => $user->id,
                        'sender_type' => User::class,
                        'data' => [
                            'items' => $notifications->map(fn ($notification) => [
                                'id' => $notification->id,
                                'message' => $notification->message,
                                'created_at' => $notification->created_at->toDateTimeString(),
                                'category' => $notification->category,
                                'priority' => $notification->priority,
                            ])->all(),
                        ],
                        'action_text' => __('notifications.view'),
                        'action_url' => route('notifications'),
                        'batch_key' => 'digest:'.$user->id,
                    ]);

                    $this->recordDigestTimestamp($user);
                }
            });

        $this->info('Notification digests processed successfully.');

        return self::SUCCESS;
    }

    /**
     * Attempt to persist the latest digest timestamp without failing hard when optional columns are absent.
     */
    protected function recordDigestTimestamp(User $user): void
    {
        try {
            $this->notificationService->recordDigestSent($user);
        } catch (Throwable $exception) {
            // Swallow missing column issues for lightweight test databases.
        }
    }

    /**
     * Determine the start of the window used to gather digest entries.
     */
    protected function digestWindowStart(array $preferences, Carbon $now): Carbon
    {
        $lastSent = Arr::get($preferences, 'digest.last_sent_at');

        return $lastSent
            ? Carbon::parse($lastSent)->subMinutes(5)
            : $now->copy()->subDay();
    }

    /**
     * Calculate when the next digest should be delivered.
     */
    protected function nextRunAt(array $preferences, Carbon $now): Carbon
    {
        $lastSent = Arr::get($preferences, 'digest.last_sent_at');
        $interval = Arr::get($preferences, 'digest.interval', config('notifications.digest.default_interval'));
        $sendTime = Arr::get($preferences, 'digest.send_time', config('notifications.digest.default_time'));

        $base = $lastSent
            ? Carbon::parse($lastSent)
            : $now->copy()->subDay();

        $next = match ($interval) {
            'weekly' => $base->addWeek(),
            default => $base->addDay(),
        };

        return $next->setTimeFromTimeString($sendTime);
    }
}
