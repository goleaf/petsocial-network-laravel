<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Automated Suspension Settings
     |--------------------------------------------------------------------------
     |
     | These options configure how the platform automatically suspends users
     | after community moderation signals. The threshold and time window can be
     | tuned through environment variables so operators can respond to the
     | community's needs without deploying new code.
     |
     */
    'auto_suspend' => [
        // Total number of reports required before the system considers suspension.
        'report_threshold' => (int) env('MODERATION_REPORT_THRESHOLD', 5),

        // Number of hours that reports are considered "recent" for suspension checks.
        'window_hours' => (int) env('MODERATION_REPORT_WINDOW_HOURS', 24),

        // Default number of days a user will be suspended when automation triggers.
        'suspension_days' => (int) env('MODERATION_AUTO_SUSPENSION_DAYS', 3),

        // Message stored alongside the suspension so admins understand why it happened.
        'reason' => env(
            'MODERATION_AUTO_SUSPENSION_REASON',
            'Automated suspension triggered by repeated community reports.'
        ),
    ],
];
