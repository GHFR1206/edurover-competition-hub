<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('runs', function (Blueprint $table) {
            // Official timing reported by the arena device (milliseconds so
            // sub-second results survive; duration_seconds stays for display).
            $table->unsignedInteger('started_ms')->nullable()->after('duration_seconds');
            $table->unsignedInteger('finished_ms')->nullable()->after('started_ms');
            $table->unsignedInteger('official_time_ms')->nullable()->after('finished_ms');
            $table->unsignedInteger('lap_count')->nullable()->after('official_time_ms');
            $table->timestamp('timing_recorded_at')->nullable()->after('lap_count');
            $table->string('timing_source')->nullable()->after('timing_recorded_at');
        });
    }

    public function down(): void
    {
        Schema::table('runs', function (Blueprint $table) {
            $table->dropColumn([
                'started_ms', 'finished_ms', 'official_time_ms',
                'lap_count', 'timing_recorded_at', 'timing_source',
            ]);
        });
    }
};
