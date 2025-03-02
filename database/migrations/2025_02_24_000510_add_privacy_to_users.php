<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('profile_visibility', ['public', 'friends', 'private'])->default('public');
            $table->enum('posts_visibility', ['public', 'friends'])->default('public');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_visibility', 'posts_visibility']);
        });
    }
};
