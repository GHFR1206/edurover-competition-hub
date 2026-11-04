<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One run = one team's attempt on one arena. Supports multi-attempt via attempt_no.
        Schema::create('runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('arena_id')->constrained('arenas')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('heat_slot_id')->nullable()->constrained('heat_slots')->nullOnDelete();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->string('status')->default('scheduled'); // RunStatus enum
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->string('outcome_reason')->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'team_id', 'attempt_no']);
            $table->index(['arena_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('runs');
    }
};
