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
        if (Schema::hasTable('form_submissions')) {
            return;
        }

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('submitted_at')->nullable()->useCurrent();
            $table->enum('newsletter', ['yes', 'no'])->default('yes');
            $table->string('unsubscribe_token', 64);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
