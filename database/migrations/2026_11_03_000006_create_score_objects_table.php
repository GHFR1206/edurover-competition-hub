<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Scoreable objectives. Fully configurable per challenge via admin.
        Schema::create('score_objects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->foreignId('capability_tag_id')->nullable()->constrained('capability_tags')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('object'); // ScoreObjectType enum
            $table->integer('points_per_unit')->default(0);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->boolean('requires_evidence')->default(false);
            $table->boolean('is_bonus')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['challenge_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_objects');
    }
};
