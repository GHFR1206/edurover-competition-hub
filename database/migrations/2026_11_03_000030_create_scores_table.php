<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A judge's score sheet for a run. Versioned and immutable once approved.
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('run_id')->constrained('runs')->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft'); // ScoreStatus enum
            $table->integer('subtotal')->default(0);
            $table->integer('penalty_total')->default(0);
            $table->integer('total')->default(0);
            $table->json('ruleset_snapshot')->nullable(); // frozen rulebook at scoring time
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->timestamps();

            $table->unique(['run_id', 'judge_id', 'version']);
            $table->unique(['run_id', 'judge_id', 'idempotency_key']);
            $table->index(['run_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
