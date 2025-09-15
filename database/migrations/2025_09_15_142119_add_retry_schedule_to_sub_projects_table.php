<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sub_projects', function (Blueprint $table) {
            $table->json('retry_schedule')->nullable()->comment('Array of retry intervals in hours for 15 attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_projects', function (Blueprint $table) {
            $table->dropColumn('retry_schedule');
        });
    }
};
