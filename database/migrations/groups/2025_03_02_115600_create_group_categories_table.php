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
        Schema::create('group_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add initial categories
        $categories = [
            ['name' => 'Dogs', 'slug' => 'dogs', 'color' => '#4CAF50', 'display_order' => 1],
            ['name' => 'Cats', 'slug' => 'cats', 'color' => '#2196F3', 'display_order' => 2],
            ['name' => 'Birds', 'slug' => 'birds', 'color' => '#FF9800', 'display_order' => 3],
            ['name' => 'Fish', 'slug' => 'fish', 'color' => '#00BCD4', 'display_order' => 4],
            ['name' => 'Reptiles', 'slug' => 'reptiles', 'color' => '#795548', 'display_order' => 5],
            ['name' => 'Small Pets', 'slug' => 'small-pets', 'color' => '#9C27B0', 'display_order' => 6],
            ['name' => 'Other', 'slug' => 'other', 'color' => '#607D8B', 'display_order' => 7],
        ];

        $table = DB::table('group_categories');
        foreach ($categories as $category) {
            $table->insert($category);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_categories');
    }
};
