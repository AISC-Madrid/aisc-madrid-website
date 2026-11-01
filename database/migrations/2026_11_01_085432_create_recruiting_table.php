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
        if (Schema::hasTable('recruiting')) {
            return;
        }

        Schema::create('recruiting', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('full_name');
            $table->string('email');
            $table->string('campus', 100)->nullable();
            $table->string('position', 100);
            $table->text('interest');
            $table->string('cohort', 20);
            $table->timestamp('submitted_at')->nullable()->useCurrent();
            $table->unique(['email', 'cohort'], 'uniq_email_cohort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiting');
    }
};
