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
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->string('category')->nullable(); // e.g., "Close Friends", "Pet Pals"
        });
    }
    public function down()
    {
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
