<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('template_name');
            $table->timestamp('sent_at')->useCurrent();
            $table->index(['contact_id', 'template_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_email_logs');
    }
};
