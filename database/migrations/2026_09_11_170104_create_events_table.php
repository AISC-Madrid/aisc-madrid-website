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
        if (Schema::hasTable('events')) {
            return;
        }

        Schema::create('events', function (Blueprint $table) {
            // Mirrors the production table (legacy schema): type and speaker are free text.
            $table->integer('id', true);
            $table->string('title_es');
            $table->string('title_en');
            $table->string('type_es', 100)->nullable();
            $table->string('type_en', 100)->nullable();
            $table->string('speaker', 100)->default('AISC Madrid');
            $table->string('company', 150)->nullable();
            $table->text('description_es')->nullable();
            $table->text('description_en')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('location')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->text('google_calendar_url')->nullable();
            $table->string('youtube_url', 355)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->boolean('requires_registration')->default(false);
            $table->boolean('reminder_enabled')->default(false);
            $table->integer('reminder_days_before')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
