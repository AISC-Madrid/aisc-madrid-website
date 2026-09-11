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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title_es');
            $table->string('title_en');
            $table->foreignId('type_id')->constrained('event_types');
            $table->text('description_es')->nullable();
            $table->text('description_en')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('location')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->string('youtube_url')->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->boolean('reminder_enabled')->default(false);
            $table->unsignedSmallInteger('reminder_days_before')->nullable();
            $table->timestamps();
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
