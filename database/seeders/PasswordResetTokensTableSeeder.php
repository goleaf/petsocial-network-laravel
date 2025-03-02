<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetTokensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('password_reset_tokens')->count() === 0) {
            $users = User::all();
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            // Create a few password reset tokens for random users
            $tokenCount = min(5, $users->count());
            $selectedUsers = $users->random($tokenCount);
            
            foreach ($selectedUsers as $user) {
                DB::table('password_reset_tokens')->insert([
                    'email' => $user->email,
                    'token' => Str::random(64),
                    'created_at' => now()->subHours(rand(1, 24)),
                ]);
            }
            
            $this->command->info('Password reset tokens seeded successfully.');
        }
    }
}
