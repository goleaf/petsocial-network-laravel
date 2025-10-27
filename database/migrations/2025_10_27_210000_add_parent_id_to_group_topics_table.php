<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add the parent_id column so topics can form nested hierarchies.
     */
    public function up(): void
    {
        Schema::table('group_topics', function (Blueprint $table): void {
            // Introducing parent_id enables hierarchical threading while preserving existing data.
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('group_topics')->cascadeOnDelete();
            $table->index('parent_id');
        });
    }

    /**
     * Roll the parent_id column back if migrations are reverted.
     */
    public function down(): void
    {
        Schema::table('group_topics', function (Blueprint $table): void {
            // Drop the hierarchical references to restore the original flat structure.
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
