<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToPetFriendshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pet_friendships') && !Schema::hasColumn('pet_friendships', 'status')) {
            Schema::table('pet_friendships', function (Blueprint $table) {
                $table->string('status')->default('accepted')->after('category');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('pet_friendships') && Schema::hasColumn('pet_friendships', 'status')) {
            Schema::table('pet_friendships', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }
}
