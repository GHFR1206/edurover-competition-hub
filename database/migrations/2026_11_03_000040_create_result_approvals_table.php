<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Maker-checker record: chief judge reviews each submitted score.
        Schema::create('result_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('score_id')->constrained('scores')->cascadeOnDelete();
            $table->foreignId('run_id')->constrained('runs')->cascadeOnDelete();
            $table->string('decision'); // approved|rejected|reopened
            $table->text('reason')->nullable();
            $table->foreignId('decided_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_approvals');
    }
};
