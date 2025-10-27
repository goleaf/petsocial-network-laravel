<?php

use App\Models\User;

/**
 * RBAC permission helper expectations for each role profile.
 */
describe('RBAC permission helpers', function () {
    it('grants administrators wildcard access to every permission', function () {
        // Create an administrator to validate wildcard permission coverage.
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        expect($admin->hasPermission('analytics.view'))->toBeTrue();
        expect($admin->hasPermission('moderation.manage'))->toBeTrue();
        expect($admin->hasPermission('any.arbitrary.permission'))->toBeTrue();
    });

    it('merges inherited permissions for moderators', function () {
        // Build a moderator to ensure inherited user abilities are respected.
        $moderator = User::factory()->create([
            'role' => 'moderator',
        ]);

        expect($moderator->hasPermission('analytics.view'))->toBeTrue();
        expect($moderator->hasPermission('analytics.view_self'))->toBeTrue();
        expect($moderator->hasPermission('moderation.review.queue'))->toBeTrue();
    });

    it('restricts members from elevated permissions', function () {
        // Generate a baseline member to confirm elevated checks fail.
        $member = User::factory()->create([
            'role' => 'user',
        ]);

        expect($member->hasPermission('analytics.view'))->toBeFalse();
        expect($member->hasPermission('moderation.review.queue'))->toBeFalse();
    });
});
