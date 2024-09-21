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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        // Add 'priority' to 'sub_projects' table
        Schema::table('sub_projects', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('description'); // Adjust position as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback changes: add 'priority' back to 'projects'
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('color'); // Adjust position as needed
        });

        // Remove 'priority' from 'sub_projects' table
        Schema::table('sub_projects', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
