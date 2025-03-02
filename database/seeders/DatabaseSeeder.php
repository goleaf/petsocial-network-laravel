<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            ProfilesTableSeeder::class,
            PetsTableSeeder::class,
            PostsTableSeeder::class,
            CommentsTableSeeder::class,
            ReactionsTableSeeder::class,
            TagsTableSeeder::class,
            PetFriendshipsTableSeeder::class,
            FriendshipsTableSeeder::class,
            PetActivitiesTableSeeder::class,
            NotificationsTableSeeder::class,
            PetNotificationsTableSeeder::class,
            GroupCategoriesTableSeeder::class,
            GroupsTableSeeder::class,
            GroupRolesTableSeeder::class,
            GroupMembersTableSeeder::class,
            GroupTopicsTableSeeder::class,
            GroupEventsTableSeeder::class,
            GroupEventAttendeesTableSeeder::class,
            PollsTableSeeder::class,
            PollOptionsTableSeeder::class,
            PollVotesTableSeeder::class,
            AttachmentsTableSeeder::class,
            ActivityLogsTableSeeder::class,
            BlocksTableSeeder::class,
            CommentReportsTableSeeder::class,
            FollowsTableSeeder::class,
            FriendRequestsTableSeeder::class,
            GroupTopicParticipantsTableSeeder::class,
            GroupTopicRepliesTableSeeder::class,
            GroupUserRolesTableSeeder::class,
            MessagesTableSeeder::class,
            PostReportsTableSeeder::class,
            PostTagTableSeeder::class,
            ReportsTableSeeder::class,
            SharesTableSeeder::class,
            UserActivitiesTableSeeder::class,
            PasswordResetTokensTableSeeder::class,
            FailedJobsTableSeeder::class,
            PersonalAccessTokensTableSeeder::class,
        ]);
    }
}
