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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('summary', 255);
            $table->string('featured_summary', 255)->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->string('availability', 20)->default('in_stock');
            $table->string('unit', 40)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'product_category_id', 'sort']);
            $table->index(['is_published', 'is_featured', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
