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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('severity', 20)->default('info')->after('action');
            $table->string('ip_address', 45)->nullable()->after('description');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->json('metadata')->nullable()->after('user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['severity', 'ip_address', 'user_agent', 'metadata']);
        });
    }
};
