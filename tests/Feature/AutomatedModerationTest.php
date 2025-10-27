<?php

use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Support\Carbon;

describe('Automated moderation pipeline', function () {
    it('suspends an author once reports reach the configured threshold', function () {
        // Configure the automation to suspend quickly so the scenario runs fast.
        config([
            'moderation.auto_suspend.report_threshold' => 2,
            'moderation.auto_suspend.window_hours' => 48,
            'moderation.auto_suspend.suspension_days' => 3,
        ]);

        $author = User::factory()->create();
        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'Inappropriate content',
        ]);

        $reporters = User::factory()->count(2)->create();

        $reporters->each(function (User $reporter) use ($post) {
            PostReport::create([
                'user_id' => $reporter->id,
                'post_id' => $post->id,
                'reason' => 'Abusive language',
            ]);
        });

        $author->refresh();

        expect($author->isSuspended())->toBeTrue();
        expect($author->suspension_reason)->toBe(config('moderation.auto_suspend.reason'));
        expect(ActivityLog::where('user_id', $author->id)->where('action', 'auto_suspend')->exists())->toBeTrue();
    });

    it('releases expired suspensions automatically', function () {
        $user = User::factory()->create();
        $user->suspend(1, 'Temporary hold for investigation');

        // Manually backdate the suspension so the expiration has already passed.
        $user->forceFill([
            'suspended_at' => Carbon::now()->subDays(2),
            'suspension_ends_at' => Carbon::now()->subDay(),
        ])->save();

        expect($user->fresh()->isSuspended())->toBeFalse();
        expect($user->fresh()->suspended_at)->toBeNull();
        expect(ActivityLog::where('user_id', $user->id)->where('action', 'auto_unsuspend')->exists())->toBeTrue();
    });
});
