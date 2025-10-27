<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Control Definitions
    |--------------------------------------------------------------------------
    |
    | Each role enumerated here describes the permissions granted to users
    | assigned to that role. Permissions may contain wildcard suffixes using
    | the ".*" convention to represent grouped capabilities. Roles may also
    | inherit from one another so shared privileges can be managed centrally.
    |
    */
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Full access to administrative and security tooling.',
            'permissions' => ['*'],
        ],
        'moderator' => [
            'label' => 'Moderator',
            'description' => 'Community moderators with content review privileges.',
            'inherits' => ['user'],
            'permissions' => [
                'moderation.*',
                'analytics.view',
            ],
        ],
        'user' => [
            'label' => 'Member',
            'description' => 'Standard members interacting with the social network.',
            'permissions' => [
                'profile.update',
                'privacy.update',
                'analytics.view_self',
                'friends.manage',
                'content.publish',
            ],
        ],
    ],
];
