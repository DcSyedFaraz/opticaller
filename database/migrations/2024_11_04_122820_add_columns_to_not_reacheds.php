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
            // $table->index('address_id');
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
            // Drop indexes first
            // $table->dropIndex(['address_id']);      // Drops not_reacheds_address_id_index
            $table->dropIndex(['attempt_count']);   // Drops not_reacheds_attempt_count_index
            $table->dropIndex(['paused_until']);    // Drops not_reacheds_paused_until_index

            // Then drop the columns
            $table->dropColumn(['attempt_count', 'paused_until']);
        });
    }
};
