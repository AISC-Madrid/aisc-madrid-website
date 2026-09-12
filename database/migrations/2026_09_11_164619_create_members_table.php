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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 50)->nullable();
            $table->string('password_hash');
            $table->enum('role', ['admin', 'events', 'web', 'finance', 'marketing'])->default('marketing');
            $table->string('mail', 50)->nullable();
            $table->string('position_es', 50)->nullable()->default('Miembro');
            $table->string('position_en', 50)->nullable()->default('Member');
            $table->string('phone', 20)->nullable();
            $table->string('dni', 9)->nullable();
            $table->text('socials')->nullable();
            $table->enum('board', ['yes', 'no'])->default('no');
            $table->enum('active', ['yes', 'no'])->nullable()->default('yes');
            $table->text('image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
