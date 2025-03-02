<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pets', function (Blueprint $table) {
            if (!Schema::hasColumn('pets', 'bio')) {
                $table->text('bio')->nullable()->after('location');
            }
            if (!Schema::hasColumn('pets', 'favorite_food')) {
                $table->string('favorite_food', 100)->nullable()->after('bio');
            }
            if (!Schema::hasColumn('pets', 'favorite_toy')) {
                $table->string('favorite_toy', 100)->nullable()->after('favorite_food');
            }
            if (!Schema::hasColumn('pets', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('favorite_toy');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pets', function (Blueprint $table) {
            $columns = [];
            
            if (Schema::hasColumn('pets', 'bio')) {
                $columns[] = 'bio';
            }
            if (Schema::hasColumn('pets', 'favorite_food')) {
                $columns[] = 'favorite_food';
            }
            if (Schema::hasColumn('pets', 'favorite_toy')) {
                $columns[] = 'favorite_toy';
            }
            if (Schema::hasColumn('pets', 'is_public')) {
                $columns[] = 'is_public';
            }
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
