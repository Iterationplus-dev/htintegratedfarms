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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('type', 20);
            $table->string('status', 20)->default('new');
            $table->string('name', 120);
            $table->string('organisation', 160)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('subject', 60)->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
