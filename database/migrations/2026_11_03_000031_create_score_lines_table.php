<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_lines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('score_id')->constrained('scores')->cascadeOnDelete();
            $table->foreignId('score_object_id')->constrained('score_objects')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('achieved')->default(false);
            $table->integer('points_per_unit_snapshot')->default(0);
            $table->integer('computed_points')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['score_id', 'score_object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_lines');
    }
};
