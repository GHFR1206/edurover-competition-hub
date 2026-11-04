<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rulebooks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('aggregation')->nullable(); // best|total|average, judge averaging, tie-break order
            $table->json('tie_break_order')->nullable();
            $table->json('penalties')->nullable();
            $table->json('retry_rules')->nullable();
            $table->string('status')->default('draft'); // draft|locked
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rulebooks');
    }
};
