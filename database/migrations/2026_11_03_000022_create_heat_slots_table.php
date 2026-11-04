<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Time slots (heats) belonging to an arena within a category.
        Schema::create('heat_slots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('arena_id')->constrained('arenas')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedInteger('slot_no');
            $table->string('label')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->unique(['arena_id', 'slot_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heat_slots');
    }
};
