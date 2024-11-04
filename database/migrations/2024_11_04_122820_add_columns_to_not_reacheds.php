<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('not_reacheds', function (Blueprint $table) {
            $table->integer('attempt_count')->default(1);
            $table->timestamp('paused_until')->nullable();
            $table->index('address_id');
            $table->index('attempt_count');
            $table->index('paused_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('not_reacheds', function (Blueprint $table) {
            $table->dropColumn(['attempt_count', 'paused_until']);
            $table->dropIndex(['address_id']);
            $table->dropIndex(['attempt_count']);
            $table->dropIndex(['paused_until']);
        });
    }
};
