<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('users')->count() === 0) {
            // Create admin user
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'role' => 'admin',
                'profile_visibility' => 'public',
                'posts_visibility' => 'public',
                'privacy_settings' => User::PRIVACY_DEFAULTS,
                'banned_at' => null,
                'suspension_reason' => null,
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'deactivated_at' => null,
                'notification_preferences' => json_encode(['email' => true, 'push' => true]),
            ]);
            
            // Create regular users
            for ($i = 1; $i <= 10; $i++) {
                User::create([
                    'name' => "User $i",
                    'email' => "user$i@example.com",
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'role' => 'user',
                    'profile_visibility' => ['public', 'friends', 'private'][rand(0, 2)],
                    'posts_visibility' => ['public', 'friends'][rand(0, 1)],
                    'privacy_settings' => User::PRIVACY_DEFAULTS,
                    'banned_at' => null,
                    'suspension_reason' => null,
                    'two_factor_enabled' => (bool)rand(0, 1),
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'deactivated_at' => null,
                    'notification_preferences' => json_encode([
                        'email' => (bool)rand(0, 1),
                        'push' => (bool)rand(0, 1),
                    ]),
                ]);
            }
            
            $this->command->info('Users seeded successfully.');
        }
    }
}
