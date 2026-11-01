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
        if (Schema::hasTable('newsletter_logs')) {
            return;
        }

        Schema::create('newsletter_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email');
            $table->string('template_name');
            $table->timestamp('sent_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_logs');
    }
};
