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
        Schema::create('quote_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('email', 190);
            $table->json('data');
            $table->timestamp('expires_at')->index();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_drafts');
    }
};
