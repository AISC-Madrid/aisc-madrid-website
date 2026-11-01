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
        if (Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->integer('id', true);
            // events.id is a signed INT (legacy schema), so the FK column must match.
            $table->integer('event_id');
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('email');
            $table->timestamp('registration_date')->useCurrent();
            $table->string('attendance_status')->nullable()->default('not_attended');
            $table->boolean('qr_email_sent')->nullable()->default(false);
            $table->boolean('reminder_sent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
