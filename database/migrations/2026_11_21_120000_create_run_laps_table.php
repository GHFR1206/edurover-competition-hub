<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-lap splits reported by the arena timer / robot. Supporting data:
        // the official result is still the aggregate written onto the run.
        Schema::create('run_laps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('run_id')->constrained('runs')->cascadeOnDelete();
            $table->unsignedInteger('lap_no');
            $table->unsignedInteger('lap_ms');
            $table->unsignedInteger('cumulative_ms');
            $table->timestamp('recorded_at')->nullable();
            $table->string('source')->default('api'); // api|operator
            $table->string('device_id')->nullable();
            $table->timestamps();

            $table->unique(['run_id', 'lap_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_laps');
    }
};
