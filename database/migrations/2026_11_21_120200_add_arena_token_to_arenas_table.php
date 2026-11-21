<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            // Arena devices authenticate with a Sanctum token owned by this
            // dedicated service account; the token can be revoked per arena.
            $table->foreignId('api_user_id')->nullable()->after('event_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('token_issued_at')->nullable()->after('api_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('api_user_id');
            $table->dropColumn('token_issued_at');
        });
    }
};
