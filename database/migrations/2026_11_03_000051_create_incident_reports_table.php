<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('arena_id')->nullable()->constrained('arenas')->nullOnDelete();
            $table->foreignId('run_id')->nullable()->constrained('runs')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('severity')->default('info'); // info|warning|critical
            $table->string('category')->nullable(); // technical|robot|network|other
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open|investigating|resolved
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
