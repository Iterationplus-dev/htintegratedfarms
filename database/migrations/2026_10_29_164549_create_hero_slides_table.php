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
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 80);
            $table->string('mobile_eyebrow', 80)->nullable();
            $table->string('title', 120);
            $table->string('body', 300);
            $table->string('mobile_body', 160)->nullable();
            $table->string('image_alt', 160);
            $table->string('image_position', 20)->default('50% 50%');
            $table->string('primary_cta_label', 40)->nullable();
            $table->string('primary_cta_url', 255)->nullable();
            $table->string('secondary_cta_label', 40)->nullable();
            $table->string('secondary_cta_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
