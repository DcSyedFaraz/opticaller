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

        Schema::table('addresses', function (Blueprint $table) {
            // Change column type from string to timestamp
            $table->text('seen')->nullable()->change();
        });
        // \DB::statement("UPDATE `addresses` SET `seen` = '1970-01-01 00:00:00'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Revert column type back to string
            $table->string('seen')->nullable()->change();
        });
    }
};
