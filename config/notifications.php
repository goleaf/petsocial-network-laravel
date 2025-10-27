<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Notification Priorities
    |--------------------------------------------------------------------------
    |
    | These priorities drive channel selection and delivery speed. The array
    | order represents escalating urgency so higher values should trigger more
    | aggressive delivery tactics.
    */
    'priorities' => ['low', 'normal', 'high', 'critical'],

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    |
    | Each priority maps to a sensible default channel mix. These defaults can
    | be overridden on a per-user or per-notification basis, but they provide a
    | baseline that ensures important messages reach members quickly.
    */
    'default_channels' => [
        'low' => ['in_app'],
        'normal' => ['in_app', 'email'],
        'high' => ['in_app', 'email', 'push'],
        'critical' => ['in_app', 'email', 'push'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Categories
    |--------------------------------------------------------------------------
    |
    | Categories power filtering, batching, and digest summaries. Each entry
    | defines a label for UI surfaces and the default priority that should be
    | applied when no explicit preference exists.
    */
    'categories' => [
        'messages' => [
            'label' => 'Direct Messages',
            'description' => 'Alerts for new private messages and replies.',
            'default_priority' => 'high',
        ],
        'friend_requests' => [
            'label' => 'Friend Connections',
            'description' => 'Friend requests, approvals, and follow events.',
            'default_priority' => 'normal',
        ],
        'engagement' => [
            'label' => 'Post Engagement',
            'description' => 'Comments, reactions, shares, and mentions.',
            'default_priority' => 'normal',
        ],
        'reminders' => [
            'label' => 'Reminders',
            'description' => 'Event reminders and scheduled prompts.',
            'default_priority' => 'low',
        ],
        'system' => [
            'label' => 'System',
            'description' => 'Security alerts and administrative updates.',
            'default_priority' => 'high',
        ],
        'digest' => [
            'label' => 'Digests',
            'description' => 'Scheduled summaries of recent activity.',
            'default_priority' => 'low',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frequency Options
    |--------------------------------------------------------------------------
    |
    | Members can tailor how often they receive notifications at each priority.
    | The keys are stored in preferences while the values are human readable
    | labels rendered in settings panels.
    */
    'frequencies' => [
        'instant' => 'Instant',
        '15_minutes' => 'Every 15 minutes',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
    ],

    /*
    |--------------------------------------------------------------------------
    | Digest Scheduling Defaults
    |--------------------------------------------------------------------------
    */
    'digest' => [
        'intervals' => ['daily', 'weekly'],
        'default_interval' => 'daily',
        'default_time' => '08:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Batching Configuration
    |--------------------------------------------------------------------------
    |
    | Similar notifications can be merged when they occur within this window.
    */
    'batching' => [
        'window_seconds' => 600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Map
    |--------------------------------------------------------------------------
    |
    | Internal channel identifiers are translated to Laravel notification
    | channel names through this mapping.
    */
    'channel_map' => [
        'email' => 'mail',
        'push' => 'broadcast',
    ],
];
