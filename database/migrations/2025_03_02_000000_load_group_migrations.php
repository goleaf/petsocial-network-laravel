<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $migrationPath = database_path('migrations/groups');
        
        if (File::isDirectory($migrationPath)) {
            $files = File::files($migrationPath);
            
            foreach ($files as $file) {
                $migration = require $file->getPathname();
                $migration->up();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $migrationPath = database_path('migrations/groups');
        
        if (File::isDirectory($migrationPath)) {
            $files = File::files($migrationPath);
            
            // Reverse order for down migrations
            $files = array_reverse($files);
            
            foreach ($files as $file) {
                $migration = require $file->getPathname();
                $migration->down();
            }
        }
    }
};
