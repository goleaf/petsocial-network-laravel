<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class PersonalAccessTokensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('personal_access_tokens')->count() === 0) {
            $users = User::all();
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            // Token names
            $tokenNames = ['API Token', 'Mobile App', 'Web App', 'Desktop App', 'Third-party Integration'];
            
            // Abilities
            $abilities = [
                ['read'],
                ['read', 'write'],
                ['read', 'write', 'delete'],
                ['*'],
                ['read', 'notifications'],
                ['read', 'write', 'admin']
            ];
            
            // Create tokens for some users
            $tokenCount = min(10, $users->count() * 2);
            
            for ($i = 0; $i < $tokenCount; $i++) {
                $user = $users->random();
                $tokenName = $tokenNames[array_rand($tokenNames)];
                $tokenAbilities = $abilities[array_rand($abilities)];
                $createdAt = now()->subDays(rand(1, 60));
                $lastUsedAt = rand(0, 1) ? $createdAt->copy()->addDays(rand(1, 30)) : null;
                $expiresAt = rand(0, 1) ? $createdAt->copy()->addDays(rand(90, 365)) : null;
                
                DB::table('personal_access_tokens')->insert([
                    'tokenable_type' => 'App\\Models\\User',
                    'tokenable_id' => $user->id,
                    'name' => $tokenName,
                    'token' => hash('sha256', Str::random(40)),
                    'abilities' => json_encode($tokenAbilities),
                    'last_used_at' => $lastUsedAt,
                    'expires_at' => $expiresAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
            
            $this->command->info('Personal access tokens seeded successfully.');
        }
    }
}
