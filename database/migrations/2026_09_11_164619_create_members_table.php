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
            $table->string('full_name');
            $table->string('password_hash')->nullable();
            $table->string('role')->nullable();
            $table->string('mail')->nullable()->unique();
            $table->string('position_es')->nullable();
            $table->string('position_en')->nullable();
            $table->string('phone')->nullable();
            $table->string('dni')->nullable();
            $table->string('socials')->nullable();
            $table->boolean('board')->default(false);
            $table->boolean('active')->default(true);
            $table->boolean('honor_member')->default(false);
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->text('honor_quote')->nullable();
            $table->string('image_path')->nullable();
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
