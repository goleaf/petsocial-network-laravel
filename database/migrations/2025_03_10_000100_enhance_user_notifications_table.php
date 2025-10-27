<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_notifications', 'category')) {
                $table->string('category')->nullable()->after('type')->index();
            }

            if (! Schema::hasColumn('user_notifications', 'priority')) {
                $table->string('priority')->default('normal')->after('category')->index();
            }

            if (! Schema::hasColumn('user_notifications', 'channels')) {
                $table->json('channels')->nullable()->after('data');
            }

            if (! Schema::hasColumn('user_notifications', 'delivered_via')) {
                $table->json('delivered_via')->nullable()->after('channels');
            }

            if (! Schema::hasColumn('user_notifications', 'batch_key')) {
                $table->string('batch_key')->nullable()->after('delivered_via')->index();
            }

            if (! Schema::hasColumn('user_notifications', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('batch_key')->index();
            }

            if (! Schema::hasColumn('user_notifications', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('scheduled_for');
            }

            if (! Schema::hasColumn('user_notifications', 'is_digest')) {
                $table->boolean('is_digest')->default(false)->after('delivered_at')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->dropColumn([
                'category',
                'priority',
                'channels',
                'delivered_via',
                'batch_key',
                'scheduled_for',
                'delivered_at',
                'is_digest',
            ]);
        });
    }
};
