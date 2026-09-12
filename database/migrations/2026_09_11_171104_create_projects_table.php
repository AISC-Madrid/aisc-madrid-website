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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title_es');
            $table->string('title_en');
            $table->string('category');
            $table->text('description_es');
            $table->text('description_en');
            $table->json('tags')->nullable();
            $table->string('team_credit')->nullable();
            $table->string('github_url')->nullable();
            $table->string('external_url')->nullable();
            $table->string('image_path')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
