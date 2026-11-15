<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production already has this column (added before the migration existed), so skip it there.
     */
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'gallery_paths')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->json('gallery_paths')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('gallery_paths');
        });
    }
};
