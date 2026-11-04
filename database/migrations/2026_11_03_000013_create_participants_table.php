<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NISN is encrypted at rest. nisn_hash is a blind index for duplicate detection.
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->text('nisn')->nullable();
            $table->string('nisn_hash', 64)->nullable()->index();
            $table->string('level')->nullable(); // Level enum
            $table->string('grade')->nullable();
            $table->string('role')->default('member'); // leader|member|reserve
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
