<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add granular privacy settings to the users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('privacy_settings')->nullable()->after('posts_visibility');
        });

        User::query()->whereNull('privacy_settings')->chunkById(100, function ($users): void {
            /** @var \App\Models\User $user */
            foreach ($users as $user) {
                $user->forceFill([
                    'privacy_settings' => User::PRIVACY_DEFAULTS,
                ])->save();
            }
        });
    }

    /**
     * Remove the granular privacy settings column from the users table.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('privacy_settings');
        });
    }
};
