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
        if (!Schema::hasColumn('addresses', 'company_name')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('company_name')->nullable();
            });
        } else {
            Schema::table('addresses', function (Blueprint $table) {
                // Make existing company_name column nullable
                $table->string('company_name')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Revert to NOT NULL if column exists
            if (Schema::hasColumn('addresses', 'company_name')) {
                $table->string('company_name')->nullable(false)->change();
            }
        });
    }
};
