<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_guest_access')) {
            return;
        }

        Schema::create('event_guest_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            // events.id is a signed INT (legacy schema), so the FK column must match.
            $table->integer('event_id');
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->unique(['guest_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_guest_access');
    }
};
