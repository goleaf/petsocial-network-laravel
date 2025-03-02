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
        Schema::rename('likes', 'reactions');
        Schema::table('reactions', function (Blueprint $table) {
            $table->string('type')->default('like'); // e.g., 'like', 'love', 'haha'
        });
    }
    public function down()
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::rename('reactions', 'likes');
    }
};
