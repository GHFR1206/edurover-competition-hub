<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('level'); // Level enum: sd|smp|sma
            $table->string('name');
            $table->string('persona')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('registration');
            $table->unsignedInteger('min_team_members')->default(1);
            $table->unsignedInteger('max_team_members')->default(3);
            $table->json('settings')->nullable(); // aggregation, tie-break, retry rules
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
