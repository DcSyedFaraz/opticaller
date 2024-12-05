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
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['starting_time', 'ending_time']);

            // Add the call_duration column
            $table->integer('call_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('starting_time')->nullable();
            $table->string('ending_time')->nullable();

            // Drop the call_duration column
            $table->dropColumn('call_duration');
        });
    }
};
