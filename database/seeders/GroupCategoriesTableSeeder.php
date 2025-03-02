<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_categories')->count() === 0) {
            $categories = [
                [
                    'name' => 'Pet Owners',
                    'slug' => 'pet-owners',
                    'description' => 'General groups for all pet owners',
                    'icon' => 'fa-paw',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Dog Lovers',
                    'slug' => 'dog-lovers',
                    'description' => 'Groups focused on dogs and dog care',
                    'icon' => 'fa-dog',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Cat Enthusiasts',
                    'slug' => 'cat-enthusiasts',
                    'description' => 'Groups focused on cats and cat care',
                    'icon' => 'fa-cat',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Bird Watchers',
                    'slug' => 'bird-watchers',
                    'description' => 'Groups focused on birds and bird care',
                    'icon' => 'fa-dove',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Exotic Pets',
                    'slug' => 'exotic-pets',
                    'description' => 'Groups focused on exotic and unusual pets',
                    'icon' => 'fa-dragon',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pet Health',
                    'slug' => 'pet-health',
                    'description' => 'Groups focused on pet health and wellness',
                    'icon' => 'fa-heartbeat',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pet Training',
                    'slug' => 'pet-training',
                    'description' => 'Groups focused on pet training and behavior',
                    'icon' => 'fa-graduation-cap',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pet Adoption',
                    'slug' => 'pet-adoption',
                    'description' => 'Groups focused on pet adoption and rescue',
                    'icon' => 'fa-home',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pet Nutrition',
                    'slug' => 'pet-nutrition',
                    'description' => 'Groups focused on pet food and nutrition',
                    'icon' => 'fa-utensils',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pet Photography',
                    'slug' => 'pet-photography',
                    'description' => 'Groups focused on pet photography and art',
                    'icon' => 'fa-camera',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
            
            DB::table('group_categories')->insert($categories);
            
            $this->command->info('Group categories seeded successfully.');
        }
    }
}
