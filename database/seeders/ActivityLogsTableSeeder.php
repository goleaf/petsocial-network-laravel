<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('activity_logs')->count() === 0) {
            $users = User::all();
            $pets = Pet::all();

            $activityTypes = [
                'login', 'logout', 'post_created', 'post_updated', 'post_deleted', 'post_scheduled',
                'comment_added', 'comment_updated', 'comment_deleted',
                'friend_request_sent', 'friend_request_accepted', 'friend_request_declined',
                'profile_updated', 'pet_added', 'pet_updated', 'pet_removed',
                'group_joined', 'group_left', 'group_created', 'group_updated', 'group_deleted',
                'security_failed_login', 'security_password_reset',
            ];

            // Create user activities
            foreach ($users as $user) {
                $activityCount = rand(3, 10);

                for ($i = 0; $i < $activityCount; $i++) {
                    $activityType = $activityTypes[array_rand($activityTypes)];
                    $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 24));

                    $ipAddress = fake()->ipv4();
                    $userAgent = fake()->userAgent();

                    $metadata = [
                        'preview' => fake()->sentence(8),
                    ];

                    if (in_array($activityType, ['login', 'logout', 'security_failed_login', 'security_password_reset'], true)) {
                        $metadata['ip_address'] = $ipAddress;
                        $metadata['user_agent'] = $userAgent;
                    }

                    if ($activityType === 'post_scheduled') {
                        $metadata['scheduled_for'] = now()->addDays(rand(1, 7))->toIso8601String();
                    }

                    $severity = match ($activityType) {
                        'security_failed_login' => 'warning',
                        'security_password_reset' => 'critical',
                        default => 'info',
                    };

                    DB::table('activity_logs')->insert([
                        'user_id' => $user->id,
                        'action' => $activityType,
                        'description' => "User {$user->name} performed action: {$activityType}",
                        'severity' => $severity,
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'metadata' => json_encode($metadata),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }

            $this->command->info('Activity logs seeded successfully.');
        }
    }
}
